<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Project;
use App\Services\CircuitAssistant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    public function show(Request $request, Project $project)
    {
        if (!$this->canAccess($request, $project)) {
            abort(403);
        }

        $messages = $project->messages()
            ->orderBy('created_at', 'asc')
            ->get();

        return view('chat', compact('project', 'messages'));
    }

    public function send(Request $request, Project $project)
    {
        if (!$this->canAccess($request, $project)) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'message' => 'required|string|max:2000',
            'image' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,gif,webp',
        ]);

        $imagePath = null;
        $imageMime = null;
        $hasImage = false;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $imageMime = $file->getMimeType();
            $imagePath = $file->store('chat-images', 'public');
            $hasImage = true;
        }

        // Save user message
        Message::create([
            'project_id' => $project->id,
            'role' => 'user',
            'content' => $request->message,
            'image_path' => $imagePath,
            'image_mime' => $imageMime,
            'has_image' => $hasImage,
            'user_ip' => $request->ip(),
        ]);

        // Get AI response with user context
        $assistant = new CircuitAssistant();
        $user = Auth::check() ? Auth::user() : ($project->user_id ? $project->user : null);
        $response = $assistant->chat($project, $request->message, $imagePath, $imageMime, $user);

        // Save assistant message
        Message::create([
            'project_id' => $project->id,
            'role' => 'assistant',
            'content' => $response,
            'user_ip' => $request->ip(),
        ]);

        // Touch project updated_at
        $project->touch();

        return response()->json([
            'success' => true,
            'response' => $response,
            'has_image' => $hasImage,
            'image_url' => $hasImage ? Storage::url($imagePath) : null,
        ]);
    }

    public function ping(Request $request)
    {
        return response()->json([
            'success' => true,
            'token' => csrf_token(),
        ]);
    }

    private function canAccess(Request $request, Project $project): bool
    {
        $sessionId = $request->cookie('fk_session_id');

        if ($project->session_id === $sessionId) {
            return true;
        }

        if (Auth::check() && $project->user_id === Auth::id()) {
            return true;
        }

        return false;
    }
}
