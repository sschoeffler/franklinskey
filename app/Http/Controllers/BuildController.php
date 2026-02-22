<?php

namespace App\Http\Controllers;

use App\Models\Build;
use App\Models\BuildPart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BuildController extends Controller
{
    public function index()
    {
        $builds = Auth::user()->builds()
            ->with('parts')
            ->orderBy('sort_order')
            ->get()
            ->map(function ($build) {
                $build->readiness_info = $build->readiness;
                return $build;
            });

        return response()->json(['builds' => $builds]);
    }

    public function show(Build $build)
    {
        if ($build->user_id !== Auth::id()) {
            abort(403);
        }

        $build->load('parts');
        $inventory = Auth::user()->inventoryItems()->orderBy('name')->get();
        $categories = \App\Models\InventoryItem::categories();

        return view('builds.show', compact('build', 'inventory', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:200',
            'description' => 'nullable|string|max:2000',
        ]);

        $build = Auth::user()->builds()->create([
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . Str::random(6),
            'description' => $request->description,
            'status' => 'planning',
        ]);

        return response()->json(['success' => true, 'build' => $build]);
    }

    public function update(Request $request, Build $build)
    {
        if ($build->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'name' => 'sometimes|string|max:200',
            'description' => 'nullable|string|max:2000',
            'instructions' => 'nullable|string|max:50000',
            'status' => 'sometimes|string|in:planning,in_progress,completed',
        ]);

        $build->update($request->only(['name', 'description', 'instructions', 'status']));

        return response()->json(['success' => true, 'build' => $build]);
    }

    public function destroy(Build $build)
    {
        if ($build->user_id !== Auth::id()) {
            abort(403);
        }

        $build->delete();

        return response()->json(['success' => true]);
    }

    public function addPart(Request $request, Build $build)
    {
        if ($build->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:200',
            'description' => 'nullable|string|max:1000',
            'category' => 'nullable|string|max:50',
            'quantity_needed' => 'nullable|integer|min:1|max:999',
            'is_optional' => 'nullable|boolean',
        ]);

        $part = $build->parts()->create([
            'name' => $request->name,
            'description' => $request->description,
            'category' => $request->category ?? 'misc',
            'quantity_needed' => $request->quantity_needed ?? 1,
            'is_optional' => $request->is_optional ?? false,
            'sort_order' => $build->parts()->count(),
        ]);

        return response()->json(['success' => true, 'part' => $part]);
    }

    public function removePart(Build $build, BuildPart $part)
    {
        if ($build->user_id !== Auth::id()) {
            abort(403);
        }

        $part->delete();

        return response()->json(['success' => true]);
    }
}
