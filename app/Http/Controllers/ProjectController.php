<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $sessionId = $this->getSessionId($request);

        $projects = Project::where('session_id', $sessionId)
            ->orderByDesc('updated_at')
            ->get();

        return view('app', compact('projects'));
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
        $sessionId = $this->getSessionId($request);
        if ($project->session_id !== $sessionId) {
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
        $sessionId = $this->getSessionId($request);
        if ($project->session_id !== $sessionId) {
            abort(403);
        }

        $project->delete();

        return response()->json(['success' => true]);
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
