@php
if (!function_exists('formatResponse')) {
function formatResponse($text) {
    if (!$text) return '';
    // Strip code markers
    $text = preg_replace('/<!--CODE_START-->[\s\S]*?<!--CODE_END-->/', '<p class="text-amber-400/60 text-xs italic">[Code prepared — ready to upload]</p>', $text);
    $text = e($text);
    $text = str_replace('&lt;p class=&quot;text-amber-400/60 text-xs italic&quot;&gt;[Code prepared — ready to upload]&lt;/p&gt;', '<p class="text-amber-400/60 text-xs italic">[Code prepared — ready to upload]</p>', $text);

    // Process line by line for tables, lists, headings
    $lines = explode("\n", $text);
    $html = '';
    $inTable = false;
    $inList = false;

    foreach ($lines as $line) {
        $trimmed = trim($line);

        // Table rows
        if (preg_match('/^\|(.+)\|$/', $trimmed)) {
            // Skip separator rows
            if (preg_match('/^\|[\s\-\|:]+\|$/', $trimmed)) continue;
            if (!$inTable) {
                if ($inList) { $html .= '</ul>'; $inList = false; }
                $html .= '<table class="w-full text-sm my-2 border-collapse"><tbody>';
                $inTable = true;
            }
            $cells = array_map('trim', explode('|', trim($trimmed, '|')));
            $html .= '<tr>';
            foreach ($cells as $cell) {
                $cell = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $cell);
                $html .= '<td class="px-2 py-1 border border-white/10">' . $cell . '</td>';
            }
            $html .= '</tr>';
            continue;
        }

        if ($inTable) { $html .= '</tbody></table>'; $inTable = false; }

        // Headings
        if (preg_match('/^#{1,3}\s+(.+)$/', $trimmed, $m)) {
            if ($inList) { $html .= '</ul>'; $inList = false; }
            $html .= '<div class="font-semibold text-amber-300 mt-3 mb-1">' . e($m[1]) . '</div>';
            continue;
        }

        // List items (- or *)
        if (preg_match('/^[\-\*]\s+(.+)$/', $trimmed, $m)) {
            if (!$inList) { $html .= '<ul class="list-disc list-inside my-1 space-y-0.5">'; $inList = true; }
            $item = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $m[1]);
            $item = preg_replace('/`([^`]+)`/', '<code class="px-1 py-0.5 bg-white/5 rounded text-amber-300 text-xs">$1</code>', $item);
            $html .= '<li>' . $item . '</li>';
            continue;
        }

        if ($inList) { $html .= '</ul>'; $inList = false; }

        // Regular line
        $line = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $line);
        $line = preg_replace('/`([^`]+)`/', '<code class="px-1 py-0.5 bg-white/5 rounded text-amber-300 text-xs">$1</code>', $line);
        $html .= $line . '<br>';
    }

    if ($inTable) $html .= '</tbody></table>';
    if ($inList) $html .= '</ul>';

    return $html;
}
}
@endphp

@extends('layouts.app')

@section('title', $project->name . " — Franklin's Key")

@section('nav-right')
<a href="/app" class="text-sm text-gray-400 hover:text-amber-400 transition">&larr; All Projects</a>
@endsection

@push('styles')
<style>
    .chat-container {
        height: calc(100vh - 180px);
        min-height: 400px;
    }

    .msg-assistant {
        background: rgba(245, 158, 11, 0.04);
        border: 1px solid rgba(245, 158, 11, 0.08);
    }

    .msg-user {
        background: rgba(6, 182, 212, 0.04);
        border: 1px solid rgba(6, 182, 212, 0.08);
    }

    .typing-dot {
        animation: typingBounce 1.4s ease-in-out infinite;
    }
    .typing-dot:nth-child(2) { animation-delay: 0.2s; }
    .typing-dot:nth-child(3) { animation-delay: 0.4s; }

    @keyframes typingBounce {
        0%, 60%, 100% { transform: translateY(0); }
        30% { transform: translateY(-6px); }
    }

    /* Hide code blocks wrapped in markers */
    .msg-content .code-hidden { display: none; }

    .msg-content p { margin-bottom: 0.75rem; }
    .msg-content p:last-child { margin-bottom: 0; }
    .msg-content strong { color: #fbbf24; font-weight: 600; }
    .msg-content br + br { display: block; content: ''; margin-top: 0.5rem; }

    .image-preview-bar {
        border-top: 1px solid rgba(255,255,255,0.06);
        background: rgba(255,255,255,0.02);
    }
</style>
@endpush

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 pb-4" x-data="chatInterface()">

    <!-- Project Header -->
    <div class="flex items-center justify-between mb-4">
        <div class="min-w-0">
            <h1 class="text-xl font-bold text-white truncate">{{ $project->name }}</h1>
            @if($project->board_type)
            <span class="text-xs text-cyan-400/70 font-medium">{{ $project->board_type }}</span>
            @endif
        </div>
    </div>

    <!-- Chat Messages -->
    <div
        class="chat-container overflow-y-auto rounded-xl border border-white/[0.06] bg-white/[0.01] p-4 space-y-4 scroll-smooth"
        x-ref="chatBox"
    >
        <!-- Welcome message -->
        <div class="msg-assistant rounded-lg px-4 py-3">
            <div class="text-xs font-semibold text-amber-400 mb-1.5">Franklin's Key</div>
            @php
                // Extract build name from auto-generated project names
                $buildName = null;
                if (preg_match('/^Help with my (.+?) build\b/i', $project->name, $m)) {
                    $buildName = $m[1];
                } elseif (preg_match('/^Build guide for (.+?) —/i', $project->name, $m)) {
                    $buildName = $m[1];
                }
            @endphp
            <div class="text-sm text-gray-300 leading-relaxed">
                @if($buildName)
                    Hey! I'm Franklin's Key &#x26A1;<br><br>
                    Let's get your <strong>{{ $buildName }}</strong> build wired up!
                    @if($project->board_type) I see you're using a <strong>{{ $project->board_type }}</strong>. @endif
                    I've got your parts list and inventory loaded — I can look up specs, check what you need, and walk you through step by step.<br><br>
                    Where would you like to start?
                @else
                    Hey! I'm Franklin's Key — your circuit-building assistant. &#x26A1;<br><br>
                    Tell me what you want to build with <strong>{{ $project->board_type ?? 'your board' }}</strong> and I'll walk you through the wiring step by step. You can also snap a photo of your parts and I'll help identify them.<br><br>
                    What would you like to build?
                @endif
            </div>
        </div>

        <!-- History messages -->
        @foreach($messages as $msg)
        <div class="{{ $msg->role === 'assistant' ? 'msg-assistant' : 'msg-user' }} rounded-lg px-4 py-3">
            <div class="text-xs font-semibold {{ $msg->role === 'assistant' ? 'text-amber-400' : 'text-cyan-400' }} mb-1.5">
                {{ $msg->role === 'assistant' ? "Franklin's Key" : 'You' }}
            </div>
            @if($msg->has_image && $msg->image_path)
            <div class="mb-2">
                <img src="{{ Storage::url($msg->image_path) }}" alt="Uploaded image" class="max-w-[200px] max-h-[150px] rounded-lg border border-white/10 object-cover">
            </div>
            @endif
            <div class="text-sm text-gray-300 leading-relaxed msg-content">{!! formatResponse($msg->content) !!}</div>
        </div>
        @endforeach

        <!-- Live messages -->
        <template x-for="(msg, index) in liveMessages" :key="'live-' + index">
            <div :class="msg.role === 'assistant' ? 'msg-assistant' : 'msg-user'" class="rounded-lg px-4 py-3">
                <div class="text-xs font-semibold mb-1.5" :class="msg.role === 'assistant' ? 'text-amber-400' : 'text-cyan-400'" x-text="msg.role === 'assistant' ? 'Franklin\'s Key' : 'You'"></div>
                <template x-if="msg.imageUrl">
                    <div class="mb-2">
                        <img :src="msg.imageUrl" alt="Uploaded image" class="max-w-[200px] max-h-[150px] rounded-lg border border-white/10 object-cover">
                    </div>
                </template>
                <div class="text-sm text-gray-300 leading-relaxed msg-content" x-html="formatResponse(msg.content)"></div>
            </div>
        </template>

        <!-- Typing indicator -->
        <div x-show="loading" x-transition class="msg-assistant rounded-lg px-4 py-3">
            <div class="text-xs font-semibold text-amber-400 mb-1.5">Franklin's Key</div>
            <div class="flex items-center gap-1.5">
                <div class="typing-dot w-2 h-2 rounded-full bg-amber-400/60"></div>
                <div class="typing-dot w-2 h-2 rounded-full bg-amber-400/60"></div>
                <div class="typing-dot w-2 h-2 rounded-full bg-amber-400/60"></div>
            </div>
        </div>
    </div>

    <!-- Image Preview -->
    <div x-show="imagePreview" x-transition class="image-preview-bar px-4 py-2 flex items-center gap-3">
        <img :src="imagePreview" class="w-12 h-12 rounded-lg object-cover border border-white/10">
        <span class="text-sm text-gray-400 truncate flex-1" x-text="imageName"></span>
        <button @click="clearImage()" class="text-gray-500 hover:text-red-400 transition text-sm">&times; Remove</button>
    </div>

    <!-- Input Area -->
    <form @submit.prevent="sendMessage" class="mt-3 flex gap-2 items-end">
        <!-- Image upload -->
        <input type="file" x-ref="imageInput" accept="image/jpeg,image/png,image/gif,image/webp" capture="environment" class="hidden" @change="handleImageSelect">
        <button
            type="button"
            @click="$refs.imageInput.click()"
            :disabled="loading"
            class="flex-shrink-0 w-11 h-11 flex items-center justify-center rounded-lg border border-white/[0.08] bg-white/[0.03] text-gray-400 hover:text-cyan-400 hover:border-cyan-500/30 transition disabled:opacity-40"
            title="Upload photo or use camera"
        >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Z" />
            </svg>
        </button>

        <input
            type="text"
            x-model="userMessage"
            :disabled="loading"
            placeholder="Describe what you want to build..."
            class="flex-1 px-4 py-3 bg-white/[0.04] border border-white/[0.08] rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-amber-500/50 focus:ring-1 focus:ring-amber-500/30 transition disabled:opacity-50"
            maxlength="2000"
            @keydown.enter.prevent="sendMessage"
        >

        <button
            type="submit"
            :disabled="loading || (!userMessage.trim() && !imageFile)"
            class="flex-shrink-0 px-5 py-3 bg-amber-500 hover:bg-amber-400 text-gray-900 font-bold rounded-lg transition disabled:opacity-40 disabled:cursor-not-allowed"
        >
            <span x-show="!loading">Send</span>
            <span x-show="loading">...</span>
        </button>
    </form>

    <!-- Error -->
    <div x-show="error" x-transition class="mt-2 text-sm text-red-400" x-text="error"></div>
</div>
@endsection

@push('scripts')
<script>
function chatInterface() {
    return {
        liveMessages: [],
        userMessage: '',
        loading: false,
        error: '',
        imageFile: null,
        imagePreview: null,
        imageName: '',
        projectSlug: '{{ $project->slug }}',

        handleImageSelect(e) {
            const file = e.target.files[0];
            if (!file) return;

            if (file.size > 5 * 1024 * 1024) {
                this.error = 'Image must be under 5MB.';
                return;
            }

            this.imageFile = file;
            this.imageName = file.name;
            this.imagePreview = URL.createObjectURL(file);
            this.error = '';
        },

        clearImage() {
            this.imageFile = null;
            this.imagePreview = null;
            this.imageName = '';
            this.$refs.imageInput.value = '';
        },

        async sendMessage() {
            const text = this.userMessage.trim();
            if ((!text && !this.imageFile) || this.loading) return;

            this.loading = true;
            this.error = '';

            // Show user message immediately
            const userMsg = {
                role: 'user',
                content: text || '(photo)',
                imageUrl: this.imagePreview,
            };
            this.liveMessages.push(userMsg);
            this.scrollToBottom();

            // Build FormData
            const formData = new FormData();
            formData.append('message', text || 'What do you see in this image?');
            if (this.imageFile) {
                formData.append('image', this.imageFile);
            }

            // Clear input
            this.userMessage = '';
            const hadImage = !!this.imageFile;
            this.clearImage();

            try {
                const res = await fetch('/api/chat/' + this.projectSlug, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                if (res.status === 419) {
                    this.error = 'Session expired. Refreshing...';
                    setTimeout(() => window.location.reload(), 1000);
                    return;
                }

                const data = await res.json();

                if (data.success) {
                    // Update user message image URL with server URL
                    if (data.image_url) {
                        userMsg.imageUrl = data.image_url;
                    }

                    this.liveMessages.push({
                        role: 'assistant',
                        content: data.response,
                    });
                } else {
                    this.error = data.error || 'Something went wrong. Please try again.';
                }
            } catch (e) {
                this.error = 'Network error. Please check your connection.';
            }

            this.loading = false;
            this.scrollToBottom();
        },

        formatResponse(text) {
            if (!text) return '';
            text = text.replace(/<!--CODE_START-->[\s\S]*?<!--CODE_END-->/g, '<p class="text-amber-400/60 text-xs italic">[Code prepared — ready to upload]</p>');

            const lines = text.split('\n');
            let html = '';
            let inTable = false;
            let inList = false;

            for (const line of lines) {
                const trimmed = line.trim();

                // Table rows
                if (/^\|(.+)\|$/.test(trimmed)) {
                    if (/^\|[\s\-|:]+\|$/.test(trimmed)) continue;
                    if (!inTable) {
                        if (inList) { html += '</ul>'; inList = false; }
                        html += '<table class="w-full text-sm my-2 border-collapse"><tbody>';
                        inTable = true;
                    }
                    const cells = trimmed.replace(/^\||\|$/g, '').split('|').map(c => c.trim());
                    html += '<tr>' + cells.map(c =>
                        '<td class="px-2 py-1 border border-white/10">' + c.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>') + '</td>'
                    ).join('') + '</tr>';
                    continue;
                }

                if (inTable) { html += '</tbody></table>'; inTable = false; }

                // Headings
                const headingMatch = trimmed.match(/^#{1,3}\s+(.+)$/);
                if (headingMatch) {
                    if (inList) { html += '</ul>'; inList = false; }
                    html += '<div class="font-semibold text-amber-300 mt-3 mb-1">' + headingMatch[1] + '</div>';
                    continue;
                }

                // List items
                const listMatch = trimmed.match(/^[-*]\s+(.+)$/);
                if (listMatch) {
                    if (!inList) { html += '<ul class="list-disc list-inside my-1 space-y-0.5">'; inList = true; }
                    let item = listMatch[1].replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
                    item = item.replace(/`([^`]+)`/g, '<code class="px-1 py-0.5 bg-white/5 rounded text-amber-300 text-xs">$1</code>');
                    html += '<li>' + item + '</li>';
                    continue;
                }

                if (inList) { html += '</ul>'; inList = false; }

                // Regular line
                let out = line.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
                out = out.replace(/`([^`]+)`/g, '<code class="px-1 py-0.5 bg-white/5 rounded text-amber-300 text-xs">$1</code>');
                html += out + '<br>';
            }

            if (inTable) html += '</tbody></table>';
            if (inList) html += '</ul>';
            return html;
        },

        scrollToBottom() {
            this.$nextTick(() => {
                const box = this.$refs.chatBox;
                if (box) box.scrollTop = box.scrollHeight;
            });
        },

        init() {
            // Scroll to bottom on load
            this.scrollToBottom();

            // CSRF refresh every 15 minutes
            setInterval(async () => {
                try {
                    const res = await fetch('/api/ping', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                    });
                    const data = await res.json();
                    if (data.token) {
                        document.querySelector('meta[name="csrf-token"]').content = data.token;
                    }
                } catch (e) {}
            }, 15 * 60 * 1000);
        }
    }
}
</script>

@endpush
