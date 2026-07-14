<x-app-layout>
    <div class="py-6 sm:py-10 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-8">

        <!-- Welcome Banner -->
        <div class="relative overflow-hidden rounded-2xl hud-border bg-surface-800 p-6 sm:p-8 glow-amber scan-line">
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute -top-10 -right-10 w-72 h-72 rounded-full bg-brand/[0.04] blur-[80px]"></div>
                <div class="absolute -bottom-20 -left-10 w-80 h-80 rounded-full bg-accent/[0.03] blur-[80px]"></div>
            </div>
            <div class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-5">
                <div>
                    <p class="text-brand text-xs font-mono font-medium mb-1 uppercase tracking-wider">Welcome back,</p>
                    <h1 class="font-display text-3xl font-bold text-white tracking-tight leading-tight">
                        {{ auth()->user()->name }}
                    </h1>
                    <p class="text-gray-400 mt-3 text-sm leading-relaxed max-w-xl">
                        Your unified AI workspace is ready. Jump into agent chat, generate images, synthesize speech, experiment with embeddings, and rerank results.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('ai.playground') }}"
                       wire:navigate
                       class="inline-flex items-center gap-2 px-5 py-3 bg-brand hover:bg-brand-light text-surface-900 font-semibold text-sm rounded-xl transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Open Playground
                    </a>
                    <button
                        x-data
                        @click="$dispatch('open-dashboard-settings')"
                        class="inline-flex items-center gap-2 px-5 py-3 bg-white/5 text-white font-semibold text-sm rounded-xl border border-white/10 hover:bg-white/10 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Settings
                    </button>
                </div>
            </div>
        </div>

        <!-- Quick launch cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
            <a href="{{ route('ai.playground') }}"
               wire:navigate
               class="group rounded-2xl p-5 bg-surface-800 border border-white/[0.06] hover:border-brand/20 transition-all duration-200">
                <div class="flex items-start justify-between mb-4">
                    <div class="p-2.5 bg-brand/10 rounded-xl border border-brand/20 group-hover:bg-brand/20 transition-colors">
                        <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-mono font-bold bg-brand/10 text-brand border border-brand/20">Chat</span>
                </div>
                <h3 class="font-semibold text-white mb-1">Agent Chat</h3>
                <p class="text-sm text-gray-500">Converse with AI agents using OpenAI, Anthropic, Gemini, and more.</p>
            </a>

            <a href="{{ route('ai.playground') }}"
               wire:navigate
               class="group rounded-2xl p-5 bg-surface-800 border border-white/[0.06] hover:border-purple-500/20 transition-all duration-200">
                <div class="flex items-start justify-between mb-4">
                    <div class="p-2.5 bg-purple-500/10 rounded-xl border border-purple-500/20 group-hover:bg-purple-500/20 transition-colors">
                        <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-mono font-bold bg-purple-500/10 text-purple-400 border border-purple-500/20">Images</span>
                </div>
                <h3 class="font-semibold text-white mb-1">Image Generation</h3>
                <p class="text-sm text-gray-500">Transform prompts into visuals with Gemini and DALL-E.</p>
            </a>

            <a href="{{ route('ai.playground') }}"
               wire:navigate
               class="group rounded-2xl p-5 bg-surface-800 border border-white/[0.06] hover:border-pink-500/20 transition-all duration-200">
                <div class="flex items-start justify-between mb-4">
                    <div class="p-2.5 bg-pink-500/10 rounded-xl border border-pink-500/20 group-hover:bg-pink-500/20 transition-colors">
                        <svg class="w-5 h-5 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
                        </svg>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-mono font-bold bg-pink-500/10 text-pink-400 border border-pink-500/20">Audio</span>
                </div>
                <h3 class="font-semibold text-white mb-1">Text to Speech</h3>
                <p class="text-sm text-gray-500">Generate lifelike audio with ElevenLabs and OpenAI.</p>
            </a>

            <a href="{{ route('ai.playground') }}"
               wire:navigate
               class="group rounded-2xl p-5 bg-surface-800 border border-white/[0.06] hover:border-accent/20 transition-all duration-200">
                <div class="flex items-start justify-between mb-4">
                    <div class="p-2.5 bg-accent/10 rounded-xl border border-accent/20 group-hover:bg-accent/20 transition-colors">
                        <svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                        </svg>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-mono font-bold bg-accent/10 text-accent border border-accent/20">Audio</span>
                </div>
                <h3 class="font-semibold text-white mb-1">Transcription</h3>
                <p class="text-sm text-gray-500">Convert audio files to text with Whisper and other models.</p>
            </a>

            <a href="{{ route('ai.playground') }}"
               wire:navigate
               class="group rounded-2xl p-5 bg-surface-800 border border-white/[0.06] hover:border-teal-500/20 transition-all duration-200">
                <div class="flex items-start justify-between mb-4">
                    <div class="p-2.5 bg-teal-500/10 rounded-xl border border-teal-500/20 group-hover:bg-teal-500/20 transition-colors">
                        <svg class="w-5 h-5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                        </svg>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-mono font-bold bg-teal-500/10 text-teal-400 border border-teal-500/20">Data</span>
                </div>
                <h3 class="font-semibold text-white mb-1">Embeddings</h3>
                <p class="text-sm text-gray-500">Generate vector embeddings for semantic search and RAG.</p>
            </a>

            <a href="{{ route('ai.playground') }}"
               wire:navigate
               class="group rounded-2xl p-5 bg-surface-800 border border-white/[0.06] hover:border-emerald-500/20 transition-all duration-200">
                <div class="flex items-start justify-between mb-4">
                    <div class="p-2.5 bg-emerald-500/10 rounded-xl border border-emerald-500/20 group-hover:bg-emerald-500/20 transition-colors">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/>
                        </svg>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-mono font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Search</span>
                </div>
                <h3 class="font-semibold text-white mb-1">Reranking</h3>
                <p class="text-sm text-gray-500">Reorder documents by semantic relevance using Cohere.</p>
            </a>
        </div>

        <!-- Panels -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-surface-800 rounded-2xl border border-white/[0.06] p-6">
                <h2 class="text-xs font-mono font-semibold text-gray-500 uppercase tracking-wider mb-4">What's new</h2>
                <p class="text-sm text-gray-400">Unified mode switcher and smarter chat history coming this week.</p>
            </div>
            <div class="bg-surface-800 rounded-2xl border border-white/[0.06] p-6">
                <h2 class="text-xs font-mono font-semibold text-gray-500 uppercase tracking-wider mb-4">Shortcuts</h2>
                <div class="space-y-2">
                    <a href="{{ route('profile') }}" wire:navigate class="block rounded-xl border border-white/5 px-3 py-2.5 text-sm text-gray-400 hover:text-white hover:bg-white/5 transition-colors">Edit profile</a>
                    <a href="{{ route('ai.playground') }}" wire:navigate
                       class="block rounded-xl border border-white/5 px-3 py-2.5 text-sm text-gray-400 hover:text-white hover:bg-white/5 transition-colors">Playground settings</a>
                    <a href="{{ route('dashboard') }}"
                       wire:navigate
                       @click="$dispatch('open-dashboard-settings')"
                       class="block rounded-xl border border-white/5 px-3 py-2.5 text-sm text-gray-400 hover:text-white hover:bg-white/5 transition-colors">Dashboard layout</a>
                </div>
            </div>
        </div>

        <!-- Dashboard edit settings panel -->
        <div
            x-data="{ open: false }"
            @open-dashboard-settings.window="open = !open"
            x-show="open"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-2"
            class="bg-surface-800 rounded-2xl border border-white/[0.06] p-6"
            style="display: none;"
        >
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xs font-mono font-semibold text-gray-500 uppercase tracking-wider">Dashboard preferences</h2>
                <button @click="open = false" class="text-gray-500 hover:text-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <p class="text-sm text-gray-400">Use this panel to tailor the dashboard layout and defaults later.</p>
            <div class="mt-4 flex justify-end">
                <button class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-brand text-surface-900 text-sm font-semibold hover:bg-brand-light transition-colors">Save preferences</button>
            </div>
        </div>

        <!-- Providers -->
        <div class="bg-surface-800 rounded-2xl border border-white/[0.06] p-6">
            <h2 class="text-xs font-mono font-semibold text-gray-500 uppercase tracking-wider mb-4">Supported AI Providers</h2>
            <div class="flex flex-wrap gap-2">
                @foreach(['OpenAI', 'Anthropic', 'Gemini', 'Groq', 'Mistral', 'DeepSeek', 'Ollama', 'Cohere', 'ElevenLabs', 'Azure OpenAI', 'OpenRouter', 'xAI', 'Jina', 'VoyageAI'] as $provider)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white/5 text-gray-400 border border-white/10">
                        {{ $provider }}
                    </span>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
