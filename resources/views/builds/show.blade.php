@extends('layouts.app')

@section('title', $build->name . " — Franklin's Key")

@section('nav-right')
<a href="/dashboard" class="text-sm text-gray-400 hover:text-amber-400 transition">&larr; Workbench</a>
@endsection

@push('styles')
<style>
    .part-row {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1rem;
        border-radius: 8px;
        border: 1px solid rgba(255,255,255,0.04);
        background: rgba(255,255,255,0.01);
        transition: all 0.2s;
    }
    .part-row:hover {
        border-color: rgba(255,255,255,0.08);
    }
    .part-have {
        border-left: 3px solid #22c55e;
    }
    .part-missing {
        border-left: 3px solid #ef4444;
    }
    .part-optional {
        border-left: 3px solid #94a3b8;
    }
    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }
    .instructions-content h1, .instructions-content h2, .instructions-content h3 {
        color: #fbbf24;
        font-weight: 700;
        margin-top: 1.5rem;
        margin-bottom: 0.75rem;
    }
    .instructions-content h1 { font-size: 1.5rem; }
    .instructions-content h2 { font-size: 1.25rem; }
    .instructions-content h3 { font-size: 1.1rem; }
    .instructions-content p { margin-bottom: 0.75rem; color: #cbd5e1; line-height: 1.8; }
    .instructions-content ul, .instructions-content ol { margin-bottom: 0.75rem; padding-left: 1.5rem; color: #cbd5e1; }
    .instructions-content li { margin-bottom: 0.5rem; line-height: 1.7; }
    .instructions-content strong { color: #fbbf24; }
    .instructions-content code {
        padding: 0.125rem 0.375rem;
        background: rgba(255,255,255,0.05);
        border-radius: 4px;
        font-size: 0.85em;
        color: #38bdf8;
    }
    .instructions-content blockquote {
        border-left: 3px solid #f59e0b;
        padding-left: 1rem;
        margin: 1rem 0;
        color: #94a3b8;
        font-style: italic;
    }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-6" x-data="buildDetail()">

    <!-- Build Header -->
    <div class="mb-6">
        <div class="flex items-start justify-between mb-2">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-white" x-text="build.name">{{ $build->name }}</h1>
                <p class="text-gray-500 text-sm mt-1" x-text="build.description">{{ $build->description }}</p>
            </div>
            <div>
                <button @click="cycleStatus()" class="status-badge"
                    :class="{
                        'bg-gray-500/10 text-gray-400 hover:bg-gray-500/20': build.status === 'planning',
                        'bg-blue-500/10 text-blue-400 hover:bg-blue-500/20': build.status === 'in_progress',
                        'bg-green-500/10 text-green-400 hover:bg-green-500/20': build.status === 'completed'
                    }"
                    x-text="build.status === 'in_progress' ? 'In Progress' : (build.status.charAt(0).toUpperCase() + build.status.slice(1))">
                </button>
            </div>
        </div>

        <!-- Readiness summary -->
        <div class="flex items-center gap-4 mt-3">
            <div class="flex-1">
                <div class="h-2 rounded-full bg-white/[0.06] overflow-hidden">
                    <div class="h-full rounded-full bg-gradient-to-r from-amber-500 to-green-500 transition-all duration-500" :style="'width: ' + readiness.percent + '%'"></div>
                </div>
            </div>
            <span class="text-sm font-mono" :class="readiness.percent === 100 ? 'text-green-400' : 'text-amber-400'" x-text="readiness.ready + '/' + readiness.total + ' parts ready'"></span>
        </div>
    </div>

    <!-- Tabs -->
    <div class="flex gap-1 mb-6 border-b border-white/[0.06]">
        <button @click="tab = 'parts'" :class="tab === 'parts' ? 'text-amber-400 border-amber-400' : 'text-gray-500 border-transparent hover:text-gray-300'" class="px-4 py-2.5 text-sm font-semibold border-b-2 transition">Parts List</button>
        <button @click="tab = 'instructions'" :class="tab === 'instructions' ? 'text-amber-400 border-amber-400' : 'text-gray-500 border-transparent hover:text-gray-300'" class="px-4 py-2.5 text-sm font-semibold border-b-2 transition">Instructions</button>
    </div>

    <!-- Parts Tab -->
    <div x-show="tab === 'parts'">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-white">Required Parts</h2>
            <button @click="showAddPartModal = true" class="px-3 py-1.5 text-xs font-semibold bg-amber-500/10 text-amber-400 rounded-lg hover:bg-amber-500/20 transition">+ Add Part</button>
        </div>

        <div class="space-y-2">
            <template x-for="part in build.parts" :key="part.id">
                <div class="part-row group" :class="part.is_optional ? 'part-optional' : (hasItem(part) ? 'part-have' : 'part-missing')">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium" :class="part.is_optional ? 'text-gray-400' : (hasItem(part) ? 'text-green-300' : 'text-white')" x-text="part.name"></span>
                            <span x-show="part.is_optional" class="text-[0.65rem] px-1.5 py-0.5 rounded-full bg-white/[0.04] text-gray-500">optional</span>
                            <span x-show="part.quantity_needed > 1" class="text-xs text-gray-500 font-mono" x-text="'x' + part.quantity_needed"></span>
                        </div>
                        <p class="text-xs text-gray-500 mt-0.5" x-text="part.description" x-show="part.description"></p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs px-2 py-0.5 rounded-full" :class="hasItem(part) ? 'bg-green-500/10 text-green-400' : 'bg-red-500/10 text-red-400'" x-text="hasItem(part) ? 'Have' : 'Need'"></span>
                        <button @click="removePart(part)" class="text-gray-600 hover:text-red-400 transition opacity-0 group-hover:opacity-100 text-xs">&times;</button>
                    </div>
                </div>
            </template>

            <template x-if="build.parts.length === 0">
                <div class="text-center py-8 text-gray-500">
                    <p class="text-sm">No parts listed yet. Add parts to track what you need.</p>
                </div>
            </template>
        </div>
    </div>

    <!-- Instructions Tab -->
    <div x-show="tab === 'instructions'">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-white">Build Instructions</h2>
            <button @click="editingInstructions = !editingInstructions" class="px-3 py-1.5 text-xs font-semibold rounded-lg transition" :class="editingInstructions ? 'bg-green-500/10 text-green-400 hover:bg-green-500/20' : 'bg-white/[0.04] text-gray-400 hover:text-white'" x-text="editingInstructions ? 'Save' : 'Edit'" @click="if(editingInstructions) saveInstructions()"></button>
        </div>

        <div x-show="!editingInstructions">
            <div x-show="build.instructions" class="instructions-content prose prose-invert max-w-none" x-html="renderMarkdown(build.instructions)"></div>
            <div x-show="!build.instructions" class="text-center py-8 text-gray-500">
                <p class="text-sm">No instructions yet. Click Edit to add build instructions.</p>
            </div>
        </div>

        <div x-show="editingInstructions">
            <textarea
                x-model="instructionsText"
                rows="20"
                class="w-full px-4 py-3 bg-white/[0.04] border border-white/[0.08] rounded-lg text-white text-sm font-mono focus:outline-none focus:border-amber-500/50 resize-y"
                placeholder="Write build instructions in Markdown..."
            ></textarea>
            <p class="text-xs text-gray-500 mt-1">Supports Markdown: **bold**, *italic*, # headings, - lists, > quotes</p>
        </div>
    </div>

    <!-- ===== ADD PART MODAL ===== -->
    <div x-show="showAddPartModal" x-transition.opacity class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4" @click.self="showAddPartModal = false">
        <div class="w-full max-w-md rounded-xl border border-white/10 bg-[#111827] p-6" @click.stop>
            <h3 class="text-lg font-bold text-white mb-4">Add Required Part</h3>
            <form @submit.prevent="addPart">
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Part Name</label>
                        <input type="text" x-model="newPart.name" required maxlength="200" class="w-full px-3 py-2 bg-white/[0.04] border border-white/[0.08] rounded-lg text-white text-sm focus:outline-none focus:border-amber-500/50" placeholder="e.g. ESP32-CAM Module">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Description (optional)</label>
                        <input type="text" x-model="newPart.description" maxlength="1000" class="w-full px-3 py-2 bg-white/[0.04] border border-white/[0.08] rounded-lg text-white text-sm focus:outline-none focus:border-amber-500/50" placeholder="Notes about this part">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">Quantity Needed</label>
                            <input type="number" x-model.number="newPart.quantity_needed" min="1" max="999" class="w-full px-3 py-2 bg-white/[0.04] border border-white/[0.08] rounded-lg text-white text-sm focus:outline-none focus:border-amber-500/50">
                        </div>
                        <div class="flex items-end">
                            <label class="flex items-center gap-2 text-sm text-gray-400 pb-2">
                                <input type="checkbox" x-model="newPart.is_optional" class="rounded border-gray-600 text-amber-500 focus:ring-amber-500/30">
                                Optional
                            </label>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-5">
                    <button type="button" @click="showAddPartModal = false" class="px-4 py-2 text-sm text-gray-400 hover:text-white transition">Cancel</button>
                    <button type="submit" :disabled="!newPart.name.trim()" class="px-4 py-2 text-sm font-bold bg-amber-500 text-gray-900 rounded-lg hover:bg-amber-400 transition disabled:opacity-40">Add Part</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function buildDetail() {
    return {
        build: @json($build),
        inventory: @json($inventory),
        tab: 'parts',
        showAddPartModal: false,
        editingInstructions: false,
        instructionsText: @json($build->instructions ?? ''),
        newPart: { name: '', description: '', quantity_needed: 1, is_optional: false },

        get readiness() {
            const parts = this.build.parts.filter(p => !p.is_optional);
            let ready = 0;
            for (const part of parts) {
                if (this.hasItem(part)) ready++;
            }
            const total = parts.length;
            return {
                ready,
                total,
                percent: total > 0 ? Math.round((ready / total) * 100) : 0,
            };
        },

        hasItem(part) {
            return this.inventory.some(item => {
                const itemName = item.name.toLowerCase();
                const partName = part.name.toLowerCase();

                if (itemName.includes(partName) || partName.includes(itemName)) return true;

                // Check individual words (handles "Jumper Wires" matching "Jumper Wire 20cm Package")
                const words = partName.split(/[\s\-\/]+/);
                let matched = 0;
                for (const w of words) {
                    const stem = w.replace(/s$/, '');
                    if (stem && stem.length > 2 && itemName.includes(stem)) matched++;
                }

                return words.length > 0 && matched === words.length;
            });
        },

        async addPart() {
            try {
                const res = await this.api('/api/builds/' + this.build.slug + '/parts', 'POST', this.newPart);
                if (res.success) {
                    this.build.parts.push(res.part);
                    this.newPart = { name: '', description: '', quantity_needed: 1, is_optional: false };
                    this.showAddPartModal = false;
                }
            } catch (e) { alert('Failed to add part.'); }
        },

        async removePart(part) {
            if (!confirm('Remove ' + part.name + '?')) return;
            try {
                await this.api('/api/builds/' + this.build.slug + '/parts/' + part.id, 'DELETE');
                this.build.parts = this.build.parts.filter(p => p.id !== part.id);
            } catch (e) { alert('Failed to remove part.'); }
        },

        async cycleStatus() {
            const statuses = ['planning', 'in_progress', 'completed'];
            const idx = statuses.indexOf(this.build.status);
            const next = statuses[(idx + 1) % statuses.length];
            try {
                await this.api('/api/builds/' + this.build.slug, 'PATCH', { status: next });
                this.build.status = next;
            } catch (e) {}
        },

        async saveInstructions() {
            try {
                await this.api('/api/builds/' + this.build.slug, 'PATCH', { instructions: this.instructionsText });
                this.build.instructions = this.instructionsText;
                this.editingInstructions = false;
            } catch (e) { alert('Failed to save.'); }
        },

        renderMarkdown(text) {
            if (!text) return '';
            // Simple markdown rendering
            let html = text
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                .replace(/^### (.+)$/gm, '<h3>$1</h3>')
                .replace(/^## (.+)$/gm, '<h2>$1</h2>')
                .replace(/^# (.+)$/gm, '<h1>$1</h1>')
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\*(.*?)\*/g, '<em>$1</em>')
                .replace(/`([^`]+)`/g, '<code>$1</code>')
                .replace(/^> (.+)$/gm, '<blockquote>$1</blockquote>')
                .replace(/^- (.+)$/gm, '<li>$1</li>')
                .replace(/^(\d+)\. (.+)$/gm, '<li>$2</li>');

            // Wrap consecutive <li> in <ul>
            html = html.replace(/(<li>.*?<\/li>\n?)+/g, (match) => '<ul>' + match + '</ul>');

            // Wrap remaining lines in <p>
            html = html.split('\n').map(line => {
                if (line.trim() === '' || line.match(/^<(h[1-3]|ul|ol|li|blockquote)/)) return line;
                return '<p>' + line + '</p>';
            }).join('\n');

            return html;
        },

        async api(url, method, body) {
            const opts = {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            };
            if (body && method !== 'GET') opts.body = JSON.stringify(body);
            const res = await fetch(url, opts);
            return res.json();
        },
    };
}
</script>
@endpush
