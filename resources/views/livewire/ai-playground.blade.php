{{-- AI Playground – Unified Chat Interface --}}
<div
    x-data="{
        showSettings: @entangle('showSettings'),
        mode: @entangle('mode'),
        modeOpen: false,

        floatChat: false,
        chatX: 0,
        chatY: 0,
        chatDrag: { active: false, startX: 0, startY: 0, originX: 0, originY: 0 },

        modes: {
            agent:         { label: 'Agent Chat',    icon: 'chat',        color: 'indigo' },
            stream:        { label: 'Streaming',      icon: 'bolt',        color: 'blue'   },
            image:         { label: 'Image Gen',      icon: 'photo',       color: 'purple' },
            audio:         { label: 'Text to Speech', icon: 'speaker',     color: 'pink'   },
            transcription: { label: 'Transcription',  icon: 'mic',         color: 'sky'    },
            embeddings:    { label: 'Embeddings',     icon: 'database',    color: 'teal'   },
            reranking:     { label: 'Reranking',      icon: 'sort',        color: 'emerald'}
        },
        get currentMode() { return this.modes[this.mode] ?? this.modes.agent; },
        scrollToBottom() {
            this.$nextTick(() => {
                const el = document.getElementById('chat-messages');
                if (el) el.scrollTop = el.scrollHeight;
            });
        },

        startChatDrag(event) {
            if (!this.floatChat) return;
            const clientX = event.clientX ?? event.touches?.[0]?.clientX ?? 0;
            const clientY = event.clientY ?? event.touches?.[0]?.clientY ?? 0;
            this.chatDrag = { active: true, startX: clientX, startY: clientY, originX: this.chatX, originY: this.chatY };
        },
        moveChatDrag(event) {
            if (!this.chatDrag.active) return;
            const clientX = event.clientX ?? event.touches?.[0]?.clientX ?? 0;
            const clientY = event.clientY ?? event.touches?.[0]?.clientY ?? 0;
            this.chatX = this.chatDrag.originX + (clientX - this.chatDrag.startX);
            this.chatY = this.chatDrag.originY + (clientY - this.chatDrag.startY);
        },
        stopChatDrag() {
            this.chatDrag.active = false;
        },
        setFloatChat(on) {
            this.floatChat = on;
            if (!on) {
                this.chatX = 0;
                this.chatY = 0;
            }
        }
    }"
    x-init="
        $watch('mode', () => scrollToBottom());
        Livewire.on('messagePushed', () => scrollToBottom());

        window.addEventListener('mousemove', moveChatDrag);
        window.addEventListener('touchmove', moveChatDrag, { passive: true });
        window.addEventListener('mouseup', stopChatDrag);
        window.addEventListener('touchend', stopChatDrag);
    "
    @keydown.escape.window="if (floatChat) { setFloatChat(false) }"
    class="flex-1 bg-surface-900 flex flex-col overflow-hidden min-h-0"
>

    {{-- ══════════════════════════════════════════════════════════════
         CHAT PANEL
    ══════════════════════════════════════════════════════════════ --}}
    <div
        class="flex-1 flex flex-col bg-surface-800 border border-white/[0.06] shadow-lg rounded-none sm:rounded-none overflow-hidden"
        :style="floatChat ? 'position:fixed; left:calc(50% + pxToRem(chatX)); top:calc(50% + pxToRem(chatY)); width:min(1100px, 96vw); height:min(860px, 92vh); z-index:50; position:fixed;' : undefined"
        style="position: relative;"
    >
        {{-- Float mode drag handle --}}
        <div class="sm:hidden flex items-center justify-between px-3 py-2 bg-surface-700 border-b border-white/10">
            <span class="text-xs font-semibold text-gray-500">Chat Panel</span>
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    class="px-2 py-1 rounded-md bg-brand text-white text-xs font-semibold"
                    @mousedown="startChatDrag($event)"
                    @touchstart.passive="startChatDrag($event)"
                >Drag to move</button>
                <button
                    type="button"
                    class="px-2 py-1 rounded-md bg-white/10 text-gray-300 text-xs font-semibold"
                    @click="setFloatChat(false)"
                >Lock</button>
            </div>
        </div>

        {{-- HEADER (also draggable in float mode) --}}
        <div class="flex-shrink-0 bg-surface-800 border-b border-white/[0.06] px-4 sm:px-6 py-3 flex items-center justify-between gap-3"
             @mousedown="startChatDrag($event)"
             @touchstart.passive="startChatDrag($event)"
             style="cursor: default;">
            {{-- Left: logo + title --}}
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 rounded-xl bg-amber-500/20 flex items-center justify-center shrink-0">
                    <svg class="w-4.5 h-4.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h1 class="text-base font-bold text-white tracking-tight leading-tight">Assistant & Dashboard</h1>
                    <p class="text-xs text-gray-500 leading-tight truncate">Chat · Image · Audio · Embeddings · Reranking</p>
                </div>
            </div>

            {{-- Right: model badge + clear + float + settings --}}
            <div class="flex items-center gap-2 shrink-0">
                @php
                    $modeProviderMap = [
                        'agent'         => [$providerAgent,         $modelAgent],
                        'stream'        => [$providerStream,        $modelStream],
                        'image'         => [$providerImage,         $modelImage],
                        'audio'         => [$providerAudio,         $modelAudio],
                        'transcription' => [$providerTranscription, $modelTranscription],
                        'embeddings'    => [$providerEmbeddings,    $modelEmbeddings],
                        'reranking'     => [$providerReranking,     $modelReranking],
                    ];
                    [$activeProvider, $activeModel] = $modeProviderMap[$mode] ?? ['openai', 'gpt-4o'];
                    $providerLabel = \App\Http\Livewire\AiPlayground::$providers[$activeProvider]['label'] ?? $activeProvider;
                @endphp
                <span class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white/5 text-xs font-medium text-gray-400 border border-white/10">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-400 inline-block"></span>
                    {{ $providerLabel }} · {{ $activeModel }}
                </span>

                {{-- Toggle float / move --}}
                <button wire:click="" @click="setFloatChat(!floatChat)"
                    :class="floatChat ? 'bg-white/10 text-white border-white/10' : 'text-gray-400 hover:text-white hover:bg-white/5 border-white/10 hover:border-white/20'"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-all border">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                    </svg>
                    <span class="hidden sm:inline" x-text="floatChat ? 'Lock' : 'Move'">Move</span>
                </button>

                {{-- Clear chat --}}
                @if(count($messages))
                <button wire:click="clearChat"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-gray-500 hover:text-red-400 hover:bg-red-500/10 border border-white/10 hover:border-red-500/20 transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    <span class="hidden sm:inline">Clear</span>
                </button>
                @endif

                {{-- Settings --}}
                <button wire:click="toggleSettings"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-all border
                        {{ $showSettings ? 'bg-brand text-white border-brand shadow-sm' : 'text-gray-400 hover:text-brand hover:bg-brand/10 border-white/10 hover:border-brand/20' }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Settings
                </button>
            </div>
        </div>

        {{-- SETTINGS PANEL --}}
        @if($showSettings)
        <div class="flex-shrink-0 bg-surface-800 border-b border-white/[0.06]" x-data="{ showKeys: false }">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 py-4 space-y-4">

                @if($settingsSaved)
                <div class="flex items-center gap-2 px-3 py-2 bg-emerald-500/10 border border-emerald-500/20 rounded-lg text-xs text-emerald-400 font-medium">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ $settingsSaved }}
                </div>
                @endif

                {{-- Current mode: Provider + Model --}}
                @php
                    $modeConfig = [
                        'agent'         => ['label'=>'Agent Chat',    'providerProp'=>'providerAgent',         'modelProp'=>'modelAgent'],
                        'stream'        => ['label'=>'Streaming',      'providerProp'=>'providerStream',        'modelProp'=>'modelStream'],
                        'image'         => ['label'=>'Image Gen',      'providerProp'=>'providerImage',         'modelProp'=>'modelImage'],
                        'audio'         => ['label'=>'Text to Speech', 'providerProp'=>'providerAudio',         'modelProp'=>'modelAudio'],
                        'transcription' => ['label'=>'Transcription',  'providerProp'=>'providerTranscription', 'modelProp'=>'modelTranscription'],
                        'embeddings'    => ['label'=>'Embeddings',     'providerProp'=>'providerEmbeddings',    'modelProp'=>'modelEmbeddings'],
                        'reranking'     => ['label'=>'Reranking',      'providerProp'=>'providerReranking',     'modelProp'=>'modelReranking'],
                    ];
                    $current = $modeConfig[$mode] ?? $modeConfig['agent'];
                @endphp

                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs font-mono text-gray-500 uppercase tracking-wider">{{ $current['label'] }} Settings</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-mono text-gray-500 uppercase tracking-wider mb-1.5">Provider</label>
                        <select wire:model.live="{{ $current['providerProp'] }}"
                            class="w-full rounded-lg border border-white/10 bg-surface-700 text-sm text-gray-300 py-2 px-3 focus:ring-2 focus:ring-brand focus:border-transparent">
                            @foreach(\App\Http\Livewire\AiPlayground::$providers as $pk => $pv)
                                <option value="{{ $pk }}">{{ $pv['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-mono text-gray-500 uppercase tracking-wider mb-1.5">Model</label>
                        <input type="text" wire:model="{{ $current['modelProp'] }}"
                            list="models-{{ $mode }}"
                            placeholder="e.g. gpt-4o, claude-3-opus…"
                            class="w-full rounded-lg border border-white/10 bg-surface-700 text-sm text-gray-300 py-2 px-3 focus:ring-2 focus:ring-brand focus:border-transparent placeholder-gray-600">
                        <datalist id="models-{{ $mode }}">
                            @foreach(\App\Http\Livewire\AiPlayground::$modelSuggestions[$this->{$current['providerProp']}] ?? [] as $m)
                                <option value="{{ $m }}">
                            @endforeach
                        </datalist>
                    </div>
                </div>

                {{-- API Keys: collapsed by default --}}
                <div class="border-t border-white/5 pt-3">
                    <button @click="showKeys = !showKeys" class="flex items-center gap-2 text-[11px] font-mono text-gray-500 uppercase tracking-wider hover:text-gray-300 transition-colors">
                        <svg class="w-3 h-3 transition-transform" :class="showKeys ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                        API Keys
                    </button>

                    <div x-show="showKeys" x-transition class="mt-3 space-y-3">
                        @php
                            $keyFields = [
                                ['prop'=>'keyOpenai', 'label'=>'OpenAI', 'placeholder'=>'sk-…'],
                                ['prop'=>'keyAnthropic', 'label'=>'Anthropic', 'placeholder'=>'sk-ant-…'],
                                ['prop'=>'keyGemini', 'label'=>'Gemini', 'placeholder'=>'AIza…'],
                                ['prop'=>'keyGroq', 'label'=>'Groq', 'placeholder'=>'gsk_…'],
                                ['prop'=>'keyMistral', 'label'=>'Mistral', 'placeholder'=>'…'],
                                ['prop'=>'keyDeepseek', 'label'=>'DeepSeek', 'placeholder'=>'sk-…'],
                                ['prop'=>'keyCohere', 'label'=>'Cohere', 'placeholder'=>'…'],
                                ['prop'=>'keyElevenLabs', 'label'=>'ElevenLabs', 'placeholder'=>'…'],
                                ['prop'=>'keyOpenrouter', 'label'=>'OpenRouter', 'placeholder'=>'sk-or-…'],
                                ['prop'=>'keyXai', 'label'=>'xAI', 'placeholder'=>'xai-…'],
                                ['prop'=>'keyJina', 'label'=>'Jina', 'placeholder'=>'…'],
                                ['prop'=>'keyVoyageai', 'label'=>'VoyageAI', 'placeholder'=>'pa-…'],
                                ['prop'=>'ollamaUrl', 'label'=>'Ollama URL', 'placeholder'=>'http://localhost:11434'],
                            ];
                        @endphp
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach($keyFields as $kf)
                            <div class="flex items-center gap-2">
                                <label class="text-xs text-gray-500 w-20 shrink-0 text-right">{{ $kf['label'] }}</label>
                                <input type="password" wire:model="{{ $kf['prop'] }}"
                                    placeholder="{{ $kf['placeholder'] }}"
                                    autocomplete="off"
                                    class="flex-1 rounded-lg border border-white/10 bg-surface-700 text-xs text-gray-300 py-1.5 px-2.5 focus:ring-2 focus:ring-brand focus:border-transparent placeholder-gray-600">
                            </div>
                            @endforeach
                        </div>
                        <p class="text-[11px] text-gray-600">Keys are session-only — never stored in database.</p>
                    </div>
                </div>

                <div class="flex justify-end pt-1">
                    <button wire:click="saveSettings"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-brand hover:bg-brand-light text-surface-900 text-sm font-semibold transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        Save
                    </button>
                </div>
            </div>
        </div>
        @endif

        {{-- CHAT MESSAGES --}}
        <div id="chat-messages"
            class="flex-1 overflow-y-auto custom-scrollbar px-4 sm:px-6 py-6 space-y-5"
            x-ref="chatMessages">
            {{-- Empty state --}}
            @if(empty($messages))
            <div class="flex flex-col items-center justify-center h-full text-center gap-4 py-16">
                <div class="w-16 h-16 rounded-2xl bg-amber-500/10 flex items-center justify-center">
                    <svg class="w-8 h-8 text-amber-500/60" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-base font-semibold text-gray-300">Start a conversation</p>
                    <p class="text-sm text-gray-500 mt-1 max-w-xs">Pick a mode from the chat bar below and send your first message.</p>
                </div>
                <div class="flex flex-wrap justify-center gap-2 mt-2">
                    @php
                        $quickModes = [
                            ['key'=>'agent', 'label'=>'Agent Chat', 'cls'=>'bg-indigo-500/10 text-indigo-400 border-indigo-500/20 hover:bg-indigo-500/20'],
                            ['key'=>'stream', 'label'=>'Streaming', 'cls'=>'bg-blue-500/10 text-blue-400 border-blue-500/20 hover:bg-blue-500/20'],
                            ['key'=>'image', 'label'=>'Image Gen', 'cls'=>'bg-purple-500/10 text-purple-400 border-purple-500/20 hover:bg-purple-500/20'],
                            ['key'=>'audio', 'label'=>'Text to Speech','cls'=>'bg-pink-500/10 text-pink-400 border-pink-500/20 hover:bg-pink-500/20'],
                            ['key'=>'embeddings','label'=>'Embeddings','cls'=>'bg-teal-500/10 text-teal-400 border-teal-500/20 hover:bg-teal-500/20'],
                            ['key'=>'reranking','label'=>'Reranking','cls'=>'bg-emerald-500/10 text-emerald-400 border-emerald-500/20 hover:bg-emerald-500/20'],
                        ];
                    @endphp
                    @foreach($quickModes as $qm)
                    <button wire:click="setMode('{{ $qm['key'] }}')"
                        class="px-3 py-1.5 rounded-full text-xs font-medium border transition-all {{ $qm['cls'] }}">
                        {{ $qm['label'] }}
                    </button>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Messages --}}
            @foreach($messages as $i => $msg)
            @php
                $isUser = $msg['role'] === 'user';
                $meta   = $msg['meta'] ?? [];
                $modeTag = $meta['mode'] ?? '';
                $modeTagColors = [
                    'Agent Chat' => 'bg-indigo-500/10 text-indigo-400',
                    'Streaming' => 'bg-blue-500/10 text-blue-400',
                    'Image Gen' => 'bg-purple-500/10 text-purple-400',
                    'Text to Speech' => 'bg-pink-500/10 text-pink-400',
                    'Transcription' => 'bg-sky-500/10 text-sky-400',
                    'Embeddings' => 'bg-teal-500/10 text-teal-400',
                    'Reranking' => 'bg-emerald-500/10 text-emerald-400',
                ];
                $tagCls = $modeTagColors[$modeTag] ?? 'bg-white/5 text-gray-400';
            @endphp

            {{-- User message --}}
            @if($isUser)
            <div class="flex justify-end gap-3 group">
                <div class="max-w-[75%] flex flex-col items-end gap-1">
                    @if($modeTag)
                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full {{ $tagCls }}">{{ $modeTag }}</span>
                    @endif

                    @if($msg['type'] === 'file')
                    <div class="flex items-center gap-2 bg-indigo-600 text-white px-4 py-2.5 rounded-2xl rounded-br-sm shadow-sm">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                        </svg>
                        <span class="text-sm font-medium">{{ $msg['content'] }}</span>
                    </div>
                    @elseif($msg['type'] === 'rerank_input')
                    <div class="bg-indigo-600 text-white px-4 py-3 rounded-2xl rounded-br-sm shadow-sm space-y-2 max-w-sm">
                        <p class="text-xs font-semibold opacity-75 uppercase tracking-wide">Reranking Query</p>
                        <p class="text-sm">{{ $msg['content'] }}</p>
                        @if(!empty($meta['documents']))
                        <div class="border-t border-indigo-500 pt-2">
                            <p class="text-xs opacity-75 mb-1">Documents:</p>
                            <p class="text-xs opacity-90 whitespace-pre-wrap leading-relaxed">{{ $meta['documents'] }}</p>
                        </div>
                        @endif
                    </div>
                    @else
                    <div class="bg-indigo-600 text-white px-4 py-2.5 rounded-2xl rounded-br-sm shadow-sm">
                        <p class="text-sm leading-relaxed">{{ $msg['content'] }}</p>
                    </div>
                    @endif
                </div>
                <div class="w-8 h-8 rounded-full bg-amber-500/20 flex items-center justify-center text-amber-500 text-xs font-bold shrink-0 mt-auto">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
            </div>

            {{-- AI message --}}
            @else
            <div class="flex gap-3 group">
                <div class="w-8 h-8 rounded-full bg-amber-500/20 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div class="max-w-[80%] flex flex-col gap-1">
                    @if(!empty($meta['provider']))
                    <div class="flex items-center gap-1.5">
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full {{ $tagCls }}">{{ $modeTag }}</span>
                        <span class="text-[10px] text-gray-400">{{ \App\Http\Livewire\AiPlayground::$providers[$meta['provider']]['label'] ?? $meta['provider'] }} · {{ $meta['model'] ?? '' }}</span>
                    </div>
                    @endif

                    @if($msg['type'] === 'error')
                    <div class="flex items-start gap-2.5 bg-red-500/10 border border-red-500/20 px-4 py-3 rounded-2xl rounded-tl-sm">
                        <svg class="w-4 h-4 text-red-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <p class="text-sm text-red-400 leading-relaxed">{{ $msg['content'] }}</p>
                    </div>
                    @elseif(in_array($msg['type'], ['text', 'stream']))
                    <div class="bg-surface-700 border border-white/[0.06] px-4 py-3 rounded-2xl rounded-tl-sm">
                        <div class="prose prose-sm max-w-none text-gray-300 leading-relaxed prose-invert">
                            @if($msg['type'] === 'stream')
                                <span wire:stream="messages.{{ $i }}.content">{{ $msg['content'] }}</span>
                                @if(empty($msg['content']))
                                <span class="inline-flex gap-1 items-center">
                                    <span class="w-1.5 h-1.5 bg-brand rounded-full animate-bounce" style="animation-delay:0ms"></span>
                                    <span class="w-1.5 h-1.5 bg-brand rounded-full animate-bounce" style="animation-delay:150ms"></span>
                                    <span class="w-1.5 h-1.5 bg-brand rounded-full animate-bounce" style="animation-delay:300ms"></span>
                                </span>
                                @endif
                            @else
                                {!! \Illuminate\Support\Str::markdown(e($msg['content'])) !!}
                            @endif
                        </div>
                    </div>
                    @elseif($msg['type'] === 'image')
                    <div class="rounded-2xl rounded-tl-sm overflow-hidden border border-white/[0.06] bg-surface-700 max-w-sm">
                        <img src="{{ $msg['content'] }}" alt="Generated image" class="w-full h-auto object-cover">
                        <div class="px-4 py-2.5 flex items-center justify-between border-t border-white/[0.06]">
                            <span class="text-xs text-gray-500">AI Generated</span>
                            <a href="{{ $msg['content'] }}" download="ai_image.png"
                                class="text-xs font-semibold text-brand hover:text-amber-400 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Download
                            </a>
                        </div>
                    </div>
                    @elseif($msg['type'] === 'audio')
                    <div class="bg-surface-700 border border-white/[0.06] px-4 py-3 rounded-2xl rounded-tl-sm min-w-[260px]">
                        <p class="text-xs font-semibold text-gray-500 mb-2">Generated Audio</p>
                        <audio controls class="w-full h-8" src="{{ $msg['content'] }}">Your browser does not support the audio element.</audio>
                    </div>
                    @elseif($msg['type'] === 'code')
                    <div class="bg-surface-700 border border-white/[0.06] rounded-2xl rounded-tl-sm p-4 max-w-lg overflow-x-auto">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-mono text-gray-500">{{ $meta['label'] ?? 'Output' }}</span>
                            <span class="px-2 py-0.5 bg-white/10 text-teal-400 text-xs rounded font-mono">JSON</span>
                        </div>
                        <p class="text-teal-300 font-mono text-xs leading-relaxed whitespace-pre-wrap">{{ $msg['content'] }}</p>
                    </div>
                    @elseif($msg['type'] === 'reranking')
                    <div class="bg-surface-700 border border-white/[0.06] rounded-2xl rounded-tl-sm p-4 min-w-[280px] max-w-md">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Reranked Results</p>
                        <ul class="space-y-2">
                            @foreach((array)$msg['content'] as $ri => $ritem)
                            <li class="flex items-start gap-2.5 bg-white/5 rounded-xl px-3 py-2.5 border border-white/[0.06]">
                                <span class="shrink-0 w-5 h-5 rounded-md bg-emerald-500/20 text-emerald-400 text-xs font-bold flex items-center justify-center mt-0.5">{{ $ri + 1 }}</span>
                                <span class="text-sm text-gray-300 leading-relaxed">{{ is_array($ritem) ? ($ritem['document'] ?? json_encode($ritem)) : $ritem }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
            </div>
            @endif
            @endforeach

            {{-- Loading --}}
            <div wire:loading wire:target="send" class="flex gap-3">
                <div class="w-8 h-8 rounded-full bg-amber-500/20 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-amber-500 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div class="bg-surface-700 border border-white/[0.06] px-4 py-3 rounded-2xl rounded-tl-sm">
                    <span class="inline-flex gap-1 items-center">
                        <span class="w-1.5 h-1.5 bg-brand rounded-full animate-bounce" style="animation-delay:0ms"></span>
                        <span class="w-1.5 h-1.5 bg-brand rounded-full animate-bounce" style="animation-delay:150ms"></span>
                        <span class="w-1.5 h-1.5 bg-brand rounded-full animate-bounce" style="animation-delay:300ms"></span>
                    </span>
                </div>
            </div>
        </div>

        {{-- CHAT INPUT --}}
        <div class="flex-shrink-0 bg-surface-800 border-t border-white/[0.06] px-4 sm:px-6 py-3">
            <div class="max-w-4xl mx-auto space-y-2">
                @if($mode === 'reranking')
                <div class="bg-surface-700 border border-white/[0.06] rounded-xl p-3 space-y-2">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Reranking Setup</p>
                    <input type="text" wire:model="rerankingQuery"
                        placeholder="Search query, e.g. PHP frameworks…"
                        class="w-full rounded-lg border border-white/10 bg-surface-700 text-sm text-gray-300 py-2 px-3 focus:ring-2 focus:ring-emerald-400 focus:border-transparent placeholder-gray-500">
                    <textarea wire:model="rerankingDocuments" rows="3"
                        placeholder="Documents to rerank, one per line…"
                        class="w-full rounded-lg border border-white/10 bg-surface-700 text-sm text-gray-300 py-2 px-3 focus:ring-2 focus:ring-emerald-400 focus:border-transparent placeholder-gray-500 resize-none"></textarea>
                </div>
                @endif

                @if($mode === 'transcription')
                <div class="bg-surface-700 border border-white/[0.06] rounded-xl p-3">
                    <label for="audio-upload" class="flex items-center gap-3 cursor-pointer group">
                        <div class="w-9 h-9 rounded-lg bg-sky-500/10 flex items-center justify-center shrink-0 group-hover:bg-sky-500/20 transition-colors">
                            <svg class="w-4.5 h-4.5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            @if($audioFile)
                                <p class="text-sm font-medium text-sky-400 truncate">{{ $audioFile->getClientOriginalName() }}</p>
                                <p class="text-xs text-gray-500">Click to change file</p>
                            @else
                                <p class="text-sm font-medium text-gray-400 group-hover:text-sky-400 transition-colors">Upload audio file</p>
                                <p class="text-xs text-gray-500">MP3, WAV, M4A up to 10MB</p>
                            @endif
                        </div>
                        @if($audioFile)
                        <span class="shrink-0 w-5 h-5 rounded-full bg-sky-500 flex items-center justify-center">
                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        </span>
                        @endif
                    </label>
                    <input id="audio-upload" wire:model="audioFile" type="file" accept="audio/*" class="sr-only">
                </div>
                @endif

                <div class="flex items-end gap-2">
                    @php
                        $modeConfig = [
                            'agent' => ['label'=>'Agent', 'color'=>'indigo'],
                            'stream' => ['label'=>'Stream', 'color'=>'blue'],
                            'image' => ['label'=>'Image', 'color'=>'purple'],
                            'audio' => ['label'=>'Speech', 'color'=>'pink'],
                            'transcription' => ['label'=>'Transcribe', 'color'=>'sky'],
                            'embeddings' => ['label'=>'Embed', 'color'=>'teal'],
                            'reranking' => ['label'=>'Rerank', 'color'=>'emerald'],
                        ];
                        $activeModeConf = $modeConfig[$mode] ?? $modeConfig['agent'];
                        $modeButtonColors = [
                            'indigo' => 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-indigo-200',
                            'blue' => 'bg-blue-600 hover:bg-blue-700 text-white shadow-blue-200',
                            'purple' => 'bg-purple-600 hover:bg-purple-700 text-white shadow-purple-200',
                            'pink' => 'bg-pink-600 hover:bg-pink-700 text-white shadow-pink-200',
                            'sky' => 'bg-sky-600 hover:bg-sky-700 text-white shadow-sky-200',
                            'teal' => 'bg-teal-600 hover:bg-teal-700 text-white shadow-teal-200',
                            'emerald' => 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-emerald-200',
                        ];
                        $activeBtnCls = 'bg-brand hover:bg-brand-light text-white';
                    @endphp
                    <div class="relative shrink-0" x-data="{ open: false }" @click.outside="open = false">
                        <button @click="open = !open"
                            class="flex items-center gap-1.5 px-3 py-2.5 rounded-xl text-xs font-semibold shadow-sm transition-all {{ $activeBtnCls }}">
                            {{ $activeModeConf['label'] }}
                            <svg class="w-3 h-3 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                            class="absolute bottom-full mb-2 left-0 w-44 bg-surface-700 rounded-xl border border-white/[0.06] shadow-lg py-1 z-50"
                            style="display:none;">
                            @foreach($modeConfig as $mk => $mv)
                            @php
                                $dropItemColors = [
                                    'indigo'=>'hover:bg-indigo-500/10 hover:text-indigo-400',
                                    'blue'=>'hover:bg-blue-500/10 hover:text-blue-400',
                                    'purple'=>'hover:bg-purple-500/10 hover:text-purple-400',
                                    'pink'=>'hover:bg-pink-500/10 hover:text-pink-400',
                                    'sky'=>'hover:bg-sky-500/10 hover:text-sky-400',
                                    'teal'=>'hover:bg-teal-500/10 hover:text-teal-400',
                                    'emerald'=>'hover:bg-emerald-500/10 hover:text-emerald-400',
                                ];
                                $dropCls = $dropItemColors[$mv['color']];
                                $isActive = $mode === $mk;
                            @endphp
                            <button wire:click="setMode('{{ $mk }}')" @click="open = false"
                                class="w-full text-left px-3.5 py-2 text-sm font-medium transition-colors flex items-center justify-between {{ $isActive ? 'text-white bg-white/5' : 'text-gray-400 '.$dropCls }}">
                                {{ $mv['label'] === 'Speech' ? 'Text to Speech' : ($mv['label'] === 'Transcribe' ? 'Transcription' : ($mv['label'] === 'Embed' ? 'Embeddings' : ($mv['label'] === 'Rerank' ? 'Reranking' : $mv['label']))) }}
                                @if($isActive)
                                <svg class="w-3.5 h-3.5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                @endif
                            </button>
                            @endforeach
                        </div>
                    </div>

                    @if($mode !== 'transcription' && $mode !== 'reranking')
                    <div class="flex-1 relative">
                        <textarea wire:model="inputText"
                            wire:keydown.enter.prevent="send"
                            rows="1"
                            placeholder="{{ match($mode) {
                                'agent' => 'Ask the agent anything…',
                                'stream' => 'Stream a response…',
                                'image' => 'Describe an image to generate…',
                                'audio' => 'Enter text to synthesize…',
                                'embeddings' => 'Enter text to embed…',
                                default => 'Type a message…'
                            } }}"
                            class="w-full rounded-xl border border-white/10 bg-surface-700 focus:bg-surface-700 focus:ring-2 focus:ring-brand focus:border-transparent transition-all resize-none py-2.5 pl-4 pr-4 text-sm text-white placeholder-gray-500 leading-relaxed"
                            style="min-height:42px; max-height:160px;"
                            oninput="this.style.height='auto'; this.style.height=Math.min(this.scrollHeight,160)+'px';"></textarea>
                    </div>
                    @else
                    <div class="flex-1"></div>
                    @endif

                    <button wire:click="send"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-60 cursor-not-allowed"
                        @if($mode === 'transcription') {{ !$audioFile ? 'disabled' : '' }} @endif
                        class="shrink-0 w-10 h-10 rounded-xl flex items-center justify-center transition-all focus:outline-none focus:ring-2 focus:ring-offset-1 {{ $activeBtnCls }} disabled:opacity-40 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="send">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </span>
                        <span wire:loading wire:target="send">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                            </svg>
                        </span>
                    </button>
                </div>

                <p class="text-[10px] text-gray-500 text-center">
                    Press <kbd class="px-1 py-0.5 bg-white/5 border border-white/10 rounded text-[10px] font-mono">Enter</kbd> to send · Press <kbd class="px-1 py-0.5 bg-white/5 border border-white/10 rounded text-[10px] font-mono">Esc</kbd> to close float chat · <button @click="setFloatChat(true)" class="underline hover:text-brand transition-colors">Move</button> to enable draggable chat
                </p>
            </div>
        </div>
    </div>
</div>
