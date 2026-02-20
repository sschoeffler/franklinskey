@extends('layouts.app')

@section('title', "Projects — Franklin's Key")

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 py-8" x-data="projectHub()">

    <!-- Header -->
    <div class="text-center mb-10">
        <h1 class="text-3xl sm:text-4xl font-bold mb-3">
            <span class="bg-gradient-to-r from-amber-400 to-yellow-300 bg-clip-text text-transparent">Your Projects</span>
        </h1>
        <p class="text-gray-400">Describe what you want to build. Franklin's Key handles the wiring and code.</p>
    </div>

    <!-- New Project Form -->
    <div class="mb-10 p-6 rounded-xl border border-white/[0.06] bg-white/[0.02] backdrop-blur-sm">
        <h2 class="text-lg font-semibold text-amber-400 mb-4">Start a New Project</h2>
        <form @submit.prevent="createProject" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-1.5">What do you want to build?</label>
                <input
                    type="text"
                    x-model="newName"
                    placeholder='e.g. "LED that blinks when it gets dark"'
                    class="w-full px-4 py-3 bg-white/[0.04] border border-white/[0.08] rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-amber-500/50 focus:ring-1 focus:ring-amber-500/30 transition"
                    maxlength="100"
                    required
                >
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-1.5">What board do you have? <span class="text-gray-500">(optional)</span></label>
                <select
                    x-model="newBoard"
                    class="w-full px-4 py-3 bg-white/[0.04] border border-white/[0.08] rounded-lg text-white focus:outline-none focus:border-amber-500/50 focus:ring-1 focus:ring-amber-500/30 transition appearance-none"
                >
                    <option value="">I'm not sure / I'll tell you later</option>
                    <option value="Arduino Uno">Arduino Uno</option>
                    <option value="Arduino Nano">Arduino Nano</option>
                    <option value="Arduino Mega">Arduino Mega</option>
                    <option value="ESP32">ESP32</option>
                    <option value="ESP8266">ESP8266</option>
                    <option value="Raspberry Pi Pico">Raspberry Pi Pico</option>
                </select>
            </div>
            <button
                type="submit"
                :disabled="creating || !newName.trim()"
                class="w-full sm:w-auto px-6 py-3 bg-amber-500 hover:bg-amber-400 text-gray-900 font-bold rounded-lg transition disabled:opacity-40 disabled:cursor-not-allowed"
            >
                <span x-show="!creating">Start Building</span>
                <span x-show="creating">Creating...</span>
            </button>
        </form>
    </div>

    <!-- Project List -->
    @if($projects->count() > 0)
    <div class="space-y-3">
        @foreach($projects as $project)
        <div class="group flex items-center justify-between p-4 rounded-xl border border-white/[0.06] bg-white/[0.02] hover:border-amber-500/20 transition">
            <a href="/project/{{ $project->slug }}" class="flex-1 min-w-0">
                <div class="font-semibold text-white truncate">{{ $project->name }}</div>
                <div class="text-sm text-gray-500 mt-0.5">
                    @if($project->board_type)
                        <span class="text-cyan-400/70">{{ $project->board_type }}</span> &middot;
                    @endif
                    {{ $project->messages()->count() }} messages &middot;
                    {{ $project->updated_at->diffForHumans() }}
                </div>
            </a>
            <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition ml-3">
                <a href="/project/{{ $project->slug }}" class="px-3 py-1.5 text-sm bg-amber-500/10 text-amber-400 rounded-lg hover:bg-amber-500/20 transition">
                    Open
                </a>
                <button
                    @click="deleteProject('{{ $project->slug }}')"
                    class="px-3 py-1.5 text-sm text-gray-500 hover:text-red-400 rounded-lg hover:bg-red-500/10 transition"
                >
                    Delete
                </button>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="text-center py-12 text-gray-500">
        <div class="text-4xl mb-3">&#x26A1;</div>
        <p>No projects yet. Describe what you want to build above!</p>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function projectHub() {
    return {
        newName: '',
        newBoard: '',
        creating: false,

        async createProject() {
            if (!this.newName.trim() || this.creating) return;
            this.creating = true;

            try {
                const res = await fetch('/api/projects', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        name: this.newName.trim(),
                        board_type: this.newBoard || null,
                    }),
                });

                const data = await res.json();
                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    alert('Failed to create project. Please try again.');
                    this.creating = false;
                }
            } catch (e) {
                alert('Something went wrong. Please try again.');
                this.creating = false;
            }
        },

        async deleteProject(slug) {
            if (!confirm('Delete this project and all its messages?')) return;

            try {
                const res = await fetch('/api/projects/' + slug, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });

                if (res.ok) {
                    window.location.reload();
                }
            } catch (e) {
                alert('Failed to delete project.');
            }
        }
    }
}
</script>
@endpush
