@extends('layouts.app')

@section('title', "Projects — Franklin's Key")

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 py-8" x-data="projectHub()">

    <!-- Header -->
    <div class="text-center mb-10">
        <h1 class="text-3xl sm:text-4xl font-bold mb-3">
            <span class="bg-gradient-to-r from-amber-400 to-yellow-300 bg-clip-text text-transparent">Circuit Assistant</span>
        </h1>
        <p class="text-gray-400">Describe what you want to build. Franklin's Key handles the wiring and code.</p>
        @auth
        <p class="text-sm text-gray-500 mt-1">Your inventory ({{ $inventoryCount }} items) and builds are shared with the assistant.</p>
        @endauth
    </div>

    <!-- New Project Form -->
    <div class="mb-10 p-6 rounded-xl border border-white/[0.06] bg-white/[0.02] backdrop-blur-sm">
        <h2 class="text-lg font-semibold text-amber-400 mb-4">Start a New Conversation</h2>
        <form @submit.prevent="createProject" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-1.5">What do you want to build or ask about?</label>
                <input
                    type="text"
                    x-model="newName"
                    placeholder='e.g. "LED that blinks when it gets dark" or "Help me fix my inventory"'
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
                    <option value="Raspberry Pi 5">Raspberry Pi 5</option>
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

    @auth
    @if($builds->count() > 0)
    <!-- Quick Start from Builds -->
    <div class="mb-8">
        <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3">Your Build Projects</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach($builds as $build)
            @php $readiness = $build->readiness; @endphp
            <button
                @click="startFromBuild('{{ e($build->name) }}', '{{ $readiness['ready'] }}', '{{ $readiness['total'] }}')"
                class="text-left p-4 rounded-xl border border-white/[0.06] bg-white/[0.02] hover:border-amber-500/20 hover:bg-amber-500/[0.03] transition group"
            >
                <div class="font-semibold text-white text-sm group-hover:text-amber-400 transition truncate">{{ $build->name }}</div>
                <div class="text-xs text-gray-500 mt-1">
                    <span class="{{ $readiness['percent'] === 100 ? 'text-green-400' : 'text-amber-400/70' }}">{{ $readiness['ready'] }}/{{ $readiness['total'] }} parts</span>
                    @if($build->description)
                    &middot; {{ Str::limit($build->description, 40) }}
                    @endif
                </div>
            </button>
            @endforeach
        </div>
    </div>
    @endif
    @endauth

    <!-- Chat Project List -->
    @if($projects->count() > 0)
    <div class="mb-4">
        <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3">Conversations</h2>
    </div>
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
        <p>No conversations yet. Start one above!</p>
        @auth
        <p class="text-sm mt-2">The assistant knows your inventory and builds — ask it anything.</p>
        @endauth
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

        startFromBuild(buildName, ready, total) {
            const missing = total - ready;
            if (missing > 0) {
                this.newName = `Help with my ${buildName} build — ${missing} parts missing`;
            } else {
                this.newName = `Build guide for ${buildName} — all parts ready!`;
            }
            this.$nextTick(() => {
                this.createProject();
            });
        },

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
