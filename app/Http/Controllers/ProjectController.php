<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $sessionId = $this->getSessionId($request);

        $query = Project::where('session_id', $sessionId);

        // Also include projects owned by the authenticated user
        if (Auth::check()) {
            $query->orWhere('user_id', Auth::id());
        }

        $projects = $query->orderByDesc('updated_at')->get();

        // Pass builds and inventory summary for authenticated users
        $builds = collect();
        $inventoryCount = 0;
        if (Auth::check()) {
            $builds = Auth::user()->builds()->with('parts')->get();
            $inventoryCount = Auth::user()->inventoryItems()->count();
        }

        return view('app', compact('projects', 'builds', 'inventoryCount'));
    }

    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'board_type' => 'nullable|string|max:50',
        ]);

        $sessionId = $this->getSessionId($request);
        $slug = Str::slug($request->name) . '-' . Str::random(6);

        $project = Project::create([
            'session_id' => $sessionId,
            'user_id' => Auth::id(),
            'name' => $request->name,
            'slug' => $slug,
            'board_type' => $request->board_type,
        ]);

        return response()->json([
            'success' => true,
            'redirect' => '/project/' . $project->slug,
        ]);
    }

    public function rename(Request $request, Project $project)
    {
        if (!$this->canAccess($request, $project)) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $project->update(['name' => $request->name]);

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request, Project $project)
    {
        if (!$this->canAccess($request, $project)) {
            abort(403);
        }

        $project->delete();

        return response()->json(['success' => true]);
    }

    private function canAccess(Request $request, Project $project): bool
    {
        $sessionId = $this->getSessionId($request);

        if ($project->session_id === $sessionId) {
            return true;
        }

        if (Auth::check() && $project->user_id === Auth::id()) {
            return true;
        }

        return false;
    }

    private function getSessionId(Request $request): string
    {
        $sessionId = $request->cookie('fk_session_id');

        if (!$sessionId) {
            $sessionId = (string) Str::uuid();
            cookie()->queue('fk_session_id', $sessionId, 60 * 24 * 365);
        }

        return $sessionId;
    }
}
