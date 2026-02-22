@extends('layouts.app')

@section('title', "Dashboard — Franklin's Key")

@push('styles')
<style>
    .panel {
        background: rgba(255,255,255,0.02);
        border: 1px solid rgba(255,255,255,0.06);
        border-radius: 12px;
    }
    .panel-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid rgba(255,255,255,0.06);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .panel-body {
        padding: 1rem 1.5rem;
    }
    .item-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.625rem 0;
        border-bottom: 1px solid rgba(255,255,255,0.04);
    }
    .item-row:last-child { border-bottom: none; }
    .cat-badge {
        display: inline-block;
        padding: 0.125rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        background: rgba(245,158,11,0.1);
        color: #fbbf24;
    }
    .status-planning { color: #94a3b8; }
    .status-in_progress { color: #38bdf8; }
    .status-completed { color: #22c55e; }
    .readiness-bar {
        height: 4px;
        border-radius: 2px;
        background: rgba(255,255,255,0.06);
        overflow: hidden;
    }
    .readiness-fill {
        height: 100%;
        border-radius: 2px;
        background: linear-gradient(90deg, #f59e0b, #22c55e);
        transition: width 0.3s;
    }
    .scan-dropzone {
        border: 2px dashed rgba(255,255,255,0.1);
        border-radius: 12px;
        padding: 2rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
    }
    .scan-dropzone:hover, .scan-dropzone.dragover {
        border-color: rgba(245,158,11,0.4);
        background: rgba(245,158,11,0.03);
    }
    .scan-preview-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.5rem 0.75rem;
        border-radius: 8px;
        background: rgba(255,255,255,0.03);
        border: 1px solid rgba(255,255,255,0.06);
    }
</style>
@endpush

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 py-6" x-data="dashboardApp()">

    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold mb-1">
            <span class="bg-gradient-to-r from-amber-400 to-yellow-300 bg-clip-text text-transparent">Workbench</span>
        </h1>
        <p class="text-gray-500 text-sm">Your components and build projects.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- ===== INVENTORY PANEL ===== -->
        <div class="panel">
            <div class="panel-header">
                <div>
                    <h2 class="text-lg font-bold text-white">Inventory</h2>
                    <p class="text-xs text-gray-500 mt-0.5" x-text="inventory.length + ' item' + (inventory.length !== 1 ? 's' : '')"></p>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="showScanModal = true" class="px-3 py-1.5 text-xs font-semibold bg-cyan-500/10 text-cyan-400 rounded-lg hover:bg-cyan-500/20 transition" title="Scan receipt or item photo">
                        &#x1F4F7; Scan
                    </button>
                    <button @click="showAddItemModal = true" class="px-3 py-1.5 text-xs font-semibold bg-amber-500/10 text-amber-400 rounded-lg hover:bg-amber-500/20 transition">
                        + Add
                    </button>
                </div>
            </div>

            <!-- Category filter -->
            <div class="px-4 py-2 flex flex-wrap gap-1.5 border-b border-white/[0.04]">
                <button @click="inventoryFilter = ''" :class="inventoryFilter === '' ? 'bg-amber-500/20 text-amber-400' : 'text-gray-500 hover:text-gray-300'" class="px-2.5 py-1 text-xs rounded-full transition">All</button>
                <template x-for="(label, key) in categories" :key="key">
                    <button @click="inventoryFilter = key" :class="inventoryFilter === key ? 'bg-amber-500/20 text-amber-400' : 'text-gray-500 hover:text-gray-300'" class="px-2.5 py-1 text-xs rounded-full transition" x-text="label"></button>
                </template>
            </div>

            <div class="panel-body max-h-[500px] overflow-y-auto">
                <template x-if="filteredInventory.length === 0">
                    <div class="text-center py-8 text-gray-500">
                        <div class="text-3xl mb-2">&#x1F4E6;</div>
                        <p class="text-sm">No items yet. Scan a receipt or add items manually.</p>
                    </div>
                </template>

                <template x-for="item in filteredInventory" :key="item.id">
                    <div class="item-row group">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-white font-medium truncate" x-text="item.name"></span>
                                <span class="cat-badge" x-text="categories[item.category] || item.category"></span>
                            </div>
                            <p class="text-xs text-gray-500 mt-0.5 truncate" x-text="item.description" x-show="item.description"></p>
                        </div>
                        <div class="flex items-center gap-3 ml-3">
                            <div class="flex items-center gap-1">
                                <button @click="updateQuantity(item, -1)" class="w-6 h-6 flex items-center justify-center text-gray-500 hover:text-red-400 rounded transition text-xs">-</button>
                                <span class="text-sm text-gray-300 w-6 text-center font-mono" x-text="item.quantity"></span>
                                <button @click="updateQuantity(item, 1)" class="w-6 h-6 flex items-center justify-center text-gray-500 hover:text-green-400 rounded transition text-xs">+</button>
                            </div>
                            <button @click="deleteItem(item)" class="text-gray-600 hover:text-red-400 transition opacity-0 group-hover:opacity-100 text-xs">&times;</button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- ===== BUILDS PANEL ===== -->
        <div class="panel">
            <div class="panel-header">
                <div>
                    <h2 class="text-lg font-bold text-white">Build Projects</h2>
                    <p class="text-xs text-gray-500 mt-0.5" x-text="builds.length + ' project' + (builds.length !== 1 ? 's' : '')"></p>
                </div>
                <button @click="showAddBuildModal = true" class="px-3 py-1.5 text-xs font-semibold bg-amber-500/10 text-amber-400 rounded-lg hover:bg-amber-500/20 transition">
                    + New Build
                </button>
            </div>

            <div class="panel-body max-h-[500px] overflow-y-auto">
                <template x-if="builds.length === 0">
                    <div class="text-center py-8 text-gray-500">
                        <div class="text-3xl mb-2">&#x1F527;</div>
                        <p class="text-sm">No build projects yet.</p>
                    </div>
                </template>

                <template x-for="build in builds" :key="build.id">
                    <a :href="'/builds/' + build.slug" class="block mb-3 p-4 rounded-xl border border-white/[0.06] bg-white/[0.01] hover:border-amber-500/20 transition">
                        <div class="flex items-start justify-between mb-2">
                            <div class="min-w-0">
                                <h3 class="text-sm font-bold text-white" x-text="build.name"></h3>
                                <p class="text-xs text-gray-500 mt-0.5 line-clamp-2" x-text="build.description"></p>
                            </div>
                            <span class="text-xs font-semibold ml-3 whitespace-nowrap"
                                :class="{
                                    'status-planning': build.status === 'planning',
                                    'status-in_progress': build.status === 'in_progress',
                                    'status-completed': build.status === 'completed'
                                }"
                                x-text="build.status === 'in_progress' ? 'In Progress' : (build.status.charAt(0).toUpperCase() + build.status.slice(1))"></span>
                        </div>

                        <!-- Readiness bar -->
                        <div class="mt-2" x-show="build.readiness_info">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs text-gray-500">Parts ready</span>
                                <span class="text-xs font-mono" :class="build.readiness_info?.percent === 100 ? 'text-green-400' : 'text-amber-400'" x-text="(build.readiness_info?.ready || 0) + '/' + (build.readiness_info?.total || 0)"></span>
                            </div>
                            <div class="readiness-bar">
                                <div class="readiness-fill" :style="'width: ' + (build.readiness_info?.percent || 0) + '%'"></div>
                            </div>
                        </div>

                        <!-- Parts preview -->
                        <div class="mt-2 flex flex-wrap gap-1" x-show="build.parts && build.parts.length > 0">
                            <template x-for="part in (build.parts || []).slice(0, 5)" :key="part.id">
                                <span class="text-[0.65rem] px-1.5 py-0.5 rounded bg-white/[0.04] text-gray-500" x-text="part.name"></span>
                            </template>
                            <span x-show="build.parts && build.parts.length > 5" class="text-[0.65rem] px-1.5 py-0.5 rounded bg-white/[0.04] text-gray-500" x-text="'+' + (build.parts.length - 5) + ' more'"></span>
                        </div>
                    </a>
                </template>
            </div>
        </div>
    </div>

    <!-- ===== ADD ITEM MODAL ===== -->
    <div x-show="showAddItemModal" x-transition.opacity class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4" @click.self="showAddItemModal = false">
        <div class="w-full max-w-md rounded-xl border border-white/10 bg-[#111827] p-6" @click.stop>
            <h3 class="text-lg font-bold text-white mb-4">Add Inventory Item</h3>
            <form @submit.prevent="addItem">
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Name</label>
                        <input type="text" x-model="newItem.name" required maxlength="200" class="w-full px-3 py-2 bg-white/[0.04] border border-white/[0.08] rounded-lg text-white text-sm focus:outline-none focus:border-amber-500/50" placeholder="e.g. ESP32-CAM Module">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Description (optional)</label>
                        <input type="text" x-model="newItem.description" maxlength="1000" class="w-full px-3 py-2 bg-white/[0.04] border border-white/[0.08] rounded-lg text-white text-sm focus:outline-none focus:border-amber-500/50" placeholder="Brief description">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">Category</label>
                            <select x-model="newItem.category" class="w-full px-3 py-2 bg-white/[0.04] border border-white/[0.08] rounded-lg text-white text-sm focus:outline-none focus:border-amber-500/50 appearance-none">
                                <template x-for="(label, key) in categories" :key="key">
                                    <option :value="key" x-text="label"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">Quantity</label>
                            <input type="number" x-model.number="newItem.quantity" min="1" max="9999" class="w-full px-3 py-2 bg-white/[0.04] border border-white/[0.08] rounded-lg text-white text-sm focus:outline-none focus:border-amber-500/50">
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-5">
                    <button type="button" @click="showAddItemModal = false" class="px-4 py-2 text-sm text-gray-400 hover:text-white transition">Cancel</button>
                    <button type="submit" :disabled="!newItem.name.trim()" class="px-4 py-2 text-sm font-bold bg-amber-500 text-gray-900 rounded-lg hover:bg-amber-400 transition disabled:opacity-40">Add Item</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===== ADD BUILD MODAL ===== -->
    <div x-show="showAddBuildModal" x-transition.opacity class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4" @click.self="showAddBuildModal = false">
        <div class="w-full max-w-md rounded-xl border border-white/10 bg-[#111827] p-6" @click.stop>
            <h3 class="text-lg font-bold text-white mb-4">New Build Project</h3>
            <form @submit.prevent="addBuild">
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Project Name</label>
                        <input type="text" x-model="newBuild.name" required maxlength="200" class="w-full px-3 py-2 bg-white/[0.04] border border-white/[0.08] rounded-lg text-white text-sm focus:outline-none focus:border-amber-500/50" placeholder="e.g. Front Dashcam">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Description (optional)</label>
                        <textarea x-model="newBuild.description" maxlength="2000" rows="3" class="w-full px-3 py-2 bg-white/[0.04] border border-white/[0.08] rounded-lg text-white text-sm focus:outline-none focus:border-amber-500/50 resize-none" placeholder="What are you building?"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-5">
                    <button type="button" @click="showAddBuildModal = false" class="px-4 py-2 text-sm text-gray-400 hover:text-white transition">Cancel</button>
                    <button type="submit" :disabled="!newBuild.name.trim()" class="px-4 py-2 text-sm font-bold bg-amber-500 text-gray-900 rounded-lg hover:bg-amber-400 transition disabled:opacity-40">Create Build</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===== SCAN MODAL ===== -->
    <div x-show="showScanModal" x-transition.opacity class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4" @click.self="closeScanModal()">
        <div class="w-full max-w-lg rounded-xl border border-white/10 bg-[#111827] p-6 max-h-[90vh] overflow-y-auto" @click.stop>
            <h3 class="text-lg font-bold text-white mb-4">&#x1F4F7; Scan Items</h3>

            <!-- Upload zone -->
            <div x-show="!scanResults">
                <p class="text-sm text-gray-400 mb-4">Take a photo of your receipt or components and we'll identify them automatically.</p>

                <div class="flex gap-3 mb-4">
                    <button @click="scanContext = 'receipt'" :class="scanContext === 'receipt' ? 'bg-amber-500/20 text-amber-400 border-amber-500/30' : 'border-white/[0.08] text-gray-400'" class="flex-1 px-3 py-2 text-sm rounded-lg border transition">Receipt</button>
                    <button @click="scanContext = 'item'" :class="scanContext === 'item' ? 'bg-cyan-500/20 text-cyan-400 border-cyan-500/30' : 'border-white/[0.08] text-gray-400'" class="flex-1 px-3 py-2 text-sm rounded-lg border transition">Component Photo</button>
                </div>

                <input type="file" x-ref="scanInput" accept="image/jpeg,image/png,image/gif,image/webp" capture="environment" class="hidden" @change="handleScan">

                <div class="scan-dropzone" @click="$refs.scanInput.click()" @dragover.prevent="$event.target.closest('.scan-dropzone').classList.add('dragover')" @dragleave="$event.target.closest('.scan-dropzone').classList.remove('dragover')" @drop.prevent="handleScanDrop($event)">
                    <div class="text-3xl mb-2" x-text="scanContext === 'receipt' ? '&#x1F9FE;' : '&#x1F50D;'"></div>
                    <p class="text-sm text-gray-400" x-text="scanContext === 'receipt' ? 'Drop receipt photo here or tap to take a photo' : 'Drop component photo here or tap to take a photo'"></p>
                </div>

                <div x-show="scanning" class="mt-4 text-center">
                    <div class="inline-flex items-center gap-2 text-amber-400 text-sm">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        Analyzing image...
                    </div>
                </div>
            </div>

            <!-- Scan results -->
            <div x-show="scanResults">
                <p class="text-sm text-gray-400 mb-3">Found <span class="text-amber-400 font-bold" x-text="scanResults?.length || 0"></span> items. Select which to add:</p>

                <div class="space-y-2 mb-4 max-h-[300px] overflow-y-auto">
                    <template x-for="(item, idx) in scanResults || []" :key="idx">
                        <label class="scan-preview-item cursor-pointer">
                            <div class="flex items-center gap-2 flex-1 min-w-0">
                                <input type="checkbox" :checked="item.selected" @change="item.selected = $event.target.checked" class="rounded border-gray-600 text-amber-500 focus:ring-amber-500/30">
                                <div class="min-w-0">
                                    <div class="text-sm text-white truncate" x-text="item.name"></div>
                                    <div class="text-xs text-gray-500 truncate" x-text="item.description"></div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 ml-2">
                                <span class="cat-badge" x-text="item.category"></span>
                                <span class="text-xs text-gray-400 font-mono" x-text="'x' + item.quantity"></span>
                            </div>
                        </label>
                    </template>
                </div>

                <div class="flex justify-between">
                    <button @click="scanResults = null" class="px-4 py-2 text-sm text-gray-400 hover:text-white transition">Scan Another</button>
                    <div class="flex gap-2">
                        <button @click="closeScanModal()" class="px-4 py-2 text-sm text-gray-400 hover:text-white transition">Cancel</button>
                        <button @click="addScannedItems()" class="px-4 py-2 text-sm font-bold bg-amber-500 text-gray-900 rounded-lg hover:bg-amber-400 transition">Add Selected</button>
                    </div>
                </div>
            </div>

            <div x-show="scanError" class="mt-3 text-sm text-red-400" x-text="scanError"></div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function dashboardApp() {
    return {
        inventory: @json($inventory),
        builds: @json($builds->map(fn($b) => array_merge($b->toArray(), ['readiness_info' => $b->readiness]))),
        categories: @json($categories),
        inventoryFilter: '',
        showAddItemModal: false,
        showAddBuildModal: false,
        showScanModal: false,
        scanning: false,
        scanContext: 'receipt',
        scanResults: null,
        scanError: '',
        newItem: { name: '', description: '', category: 'misc', quantity: 1 },
        newBuild: { name: '', description: '' },

        get filteredInventory() {
            if (!this.inventoryFilter) return this.inventory;
            return this.inventory.filter(i => i.category === this.inventoryFilter);
        },

        async addItem() {
            try {
                const res = await this.api('/api/inventory', 'POST', this.newItem);
                if (res.success) {
                    this.inventory.push(res.item);
                    this.inventory.sort((a, b) => a.name.localeCompare(b.name));
                    this.newItem = { name: '', description: '', category: 'misc', quantity: 1 };
                    this.showAddItemModal = false;
                    this.refreshBuilds();
                }
            } catch (e) { alert('Failed to add item.'); }
        },

        async updateQuantity(item, delta) {
            const newQty = item.quantity + delta;
            if (newQty < 0) return;
            if (newQty === 0) {
                this.deleteItem(item);
                return;
            }
            item.quantity = newQty;
            try {
                await this.api('/api/inventory/' + item.id, 'PATCH', { quantity: newQty });
                this.refreshBuilds();
            } catch (e) { item.quantity -= delta; }
        },

        async deleteItem(item) {
            if (!confirm('Remove ' + item.name + '?')) return;
            try {
                await this.api('/api/inventory/' + item.id, 'DELETE');
                this.inventory = this.inventory.filter(i => i.id !== item.id);
                this.refreshBuilds();
            } catch (e) { alert('Failed to delete.'); }
        },

        async addBuild() {
            try {
                const res = await this.api('/api/builds', 'POST', this.newBuild);
                if (res.success) {
                    res.build.parts = [];
                    res.build.readiness_info = { ready: 0, total: 0, percent: 0 };
                    this.builds.push(res.build);
                    this.newBuild = { name: '', description: '' };
                    this.showAddBuildModal = false;
                }
            } catch (e) { alert('Failed to create build.'); }
        },

        async refreshBuilds() {
            try {
                const res = await this.api('/api/builds', 'GET');
                if (res.builds) this.builds = res.builds;
            } catch (e) {}
        },

        handleScan(e) {
            const file = e.target.files[0];
            if (file) this.doScan(file);
        },

        handleScanDrop(e) {
            e.target.closest('.scan-dropzone')?.classList.remove('dragover');
            const file = e.dataTransfer.files[0];
            if (file) this.doScan(file);
        },

        async doScan(file) {
            if (file.size > 10 * 1024 * 1024) {
                this.scanError = 'Image must be under 10MB.';
                return;
            }
            this.scanning = true;
            this.scanError = '';
            this.scanResults = null;

            const formData = new FormData();
            formData.append('image', file);
            formData.append('context', this.scanContext);

            try {
                const res = await fetch('/api/inventory/scan', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                const data = await res.json();
                if (data.success && data.items) {
                    this.scanResults = data.items.map(i => ({ ...i, selected: true }));
                } else {
                    this.scanError = data.error || 'Could not identify items.';
                }
            } catch (e) {
                this.scanError = 'Network error. Please try again.';
            }

            this.scanning = false;
            if (this.$refs.scanInput) this.$refs.scanInput.value = '';
        },

        async addScannedItems() {
            const selected = (this.scanResults || []).filter(i => i.selected);
            if (selected.length === 0) return;

            try {
                const res = await this.api('/api/inventory/bulk-add', 'POST', { items: selected });
                if (res.success) {
                    this.inventory.push(...res.items);
                    this.inventory.sort((a, b) => a.name.localeCompare(b.name));
                    this.closeScanModal();
                    this.refreshBuilds();
                }
            } catch (e) { alert('Failed to add items.'); }
        },

        closeScanModal() {
            this.showScanModal = false;
            this.scanResults = null;
            this.scanError = '';
            this.scanning = false;
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
