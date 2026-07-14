<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel AI — Experiment with AI Models</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&family=figtree:400,500,600,700;800&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased font-sans bg-surface-900 text-gray-200 overflow-x-hidden">

    <!-- Ambient background -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden">
        <div class="absolute top-[-200px] left-[-200px] w-[600px] h-[600px] rounded-full bg-brand/[0.04] blur-[120px]"></div>
        <div class="absolute bottom-[-200px] right-[-200px] w-[500px] h-[500px] rounded-full bg-accent/[0.04] blur-[120px]"></div>
    </div>

    <!-- Nav -->
    <header class="relative z-10 border-b border-white/5 bg-surface-800/50 backdrop-blur-xl">
        <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-surface-700 border border-brand/20 flex items-center justify-center">
                    <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <span class="font-display font-semibold text-white text-[15px]">Laravel AI</span>
            </div>

            <nav class="flex items-center gap-3">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ route('dashboard') }}"
                            class="px-4 py-2 text-sm font-medium text-gray-400 hover:text-white transition-colors">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                            class="px-4 py-2 text-sm font-medium text-gray-400 hover:text-white transition-colors">
                            Sign in
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                                class="px-4 py-2 text-sm font-semibold text-surface-900 bg-brand hover:bg-brand-light rounded-lg transition-colors">
                                Get started
                            </a>
                        @endif
                    @endauth
                @endif
            </nav>
        </div>
    </header>

    <!-- Hero -->
    <section class="relative z-10 pt-24 pb-20 px-6 text-center">
        <div class="max-w-4xl mx-auto">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-brand/10 border border-brand/20 text-brand text-xs font-medium mb-8">
                <span class="w-1.5 h-1.5 rounded-full bg-brand status-dot"></span>
                Powered by Laravel AI · 14 providers supported
            </div>

            <h1 class="font-display text-5xl sm:text-6xl lg:text-7xl font-extrabold tracking-tight leading-[1.05] mb-6">
                <span class="text-white">Experiment with</span><br>
                <span class="text-brand">AI models</span>
                <span class="text-white"> freely</span>
            </h1>

            <p class="text-lg text-gray-400 max-w-2xl mx-auto mb-10 leading-relaxed">
                A full-featured Laravel playground for AI agents, image generation, text-to-speech, transcription, embeddings, and semantic reranking — all in one place.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                @auth
                    <a href="{{ route('ai.playground') }}"
                        class="inline-flex items-center gap-2 px-6 py-3.5 bg-brand hover:bg-brand-light text-surface-900 font-semibold rounded-xl transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Open Playground
                    </a>
                @else
                    <a href="{{ route('register') }}"
                        class="inline-flex items-center gap-2 px-6 py-3.5 bg-brand hover:bg-brand-light text-surface-900 font-semibold rounded-xl transition-all">
                        Start experimenting
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                    <a href="{{ route('login') }}"
                        class="inline-flex items-center gap-2 px-6 py-3.5 bg-white/5 hover:bg-white/10 border border-white/10 text-white font-medium rounded-xl transition-all">
                        Sign in
                    </a>
                @endauth
            </div>
        </div>
    </section>

    <!-- Feature Grid -->
    <section class="relative z-10 py-16 px-6">
        <div class="max-w-6xl mx-auto">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

                <div class="group bg-surface-800 hover:bg-surface-700 border border-white/[0.06] hover:border-brand/20 rounded-2xl p-6 transition-all duration-300">
                    <div class="w-10 h-10 rounded-xl bg-brand/10 border border-brand/20 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-white mb-2">Agent Chat</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">Converse with AI agents. Supports streaming responses and multi-turn conversations.</p>
                </div>

                <div class="group bg-surface-800 hover:bg-surface-700 border border-white/[0.06] hover:border-purple-500/20 rounded-2xl p-6 transition-all duration-300">
                    <div class="w-10 h-10 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-white mb-2">Image Generation</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">Turn text prompts into stunning visuals with Gemini, DALL-E, and other image models.</p>
                </div>

                <div class="group bg-surface-800 hover:bg-surface-700 border border-white/[0.06] hover:border-pink-500/20 rounded-2xl p-6 transition-all duration-300">
                    <div class="w-10 h-10 rounded-xl bg-pink-500/10 border border-pink-500/20 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-white mb-2">Text to Speech</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">Synthesize lifelike audio from any text using ElevenLabs and OpenAI TTS.</p>
                </div>

                <div class="group bg-surface-800 hover:bg-surface-700 border border-white/[0.06] hover:border-accent/20 rounded-2xl p-6 transition-all duration-300">
                    <div class="w-10 h-10 rounded-xl bg-accent/10 border border-accent/20 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-white mb-2">Transcription</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">Upload audio files and get accurate transcriptions powered by Whisper.</p>
                </div>

                <div class="group bg-surface-800 hover:bg-surface-700 border border-white/[0.06] hover:border-teal-500/20 rounded-2xl p-6 transition-all duration-300">
                    <div class="w-10 h-10 rounded-xl bg-teal-500/10 border border-teal-500/20 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-white mb-2">Embeddings</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">Generate dense vector representations for semantic search and RAG pipelines.</p>
                </div>

                <div class="group bg-surface-800 hover:bg-surface-700 border border-white/[0.06] hover:border-emerald-500/20 rounded-2xl p-6 transition-all duration-300">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-white mb-2">Reranking</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">Order documents by semantic relevance to a query using Cohere's reranking API.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Providers Strip -->
    <section class="relative z-10 py-12 px-6 border-t border-white/5">
        <div class="max-w-6xl mx-auto text-center">
            <p class="text-xs font-medium text-gray-600 uppercase tracking-widest mb-6">Supported Providers</p>
            <div class="flex flex-wrap items-center justify-center gap-3">
                @foreach(['OpenAI', 'Anthropic', 'Gemini', 'Groq', 'Mistral', 'DeepSeek', 'Ollama', 'Cohere', 'ElevenLabs', 'Azure OpenAI', 'OpenRouter', 'xAI', 'Jina', 'VoyageAI'] as $provider)
                    <span class="px-3 py-1.5 rounded-full bg-white/5 border border-white/10 text-gray-400 text-xs font-medium">
                        {{ $provider }}
                    </span>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="relative z-10 py-8 px-6 border-t border-white/5 text-center">
        <p class="text-xs text-gray-600 font-mono">
            Laravel v{{ Illuminate\Foundation\Application::VERSION }} · PHP v{{ PHP_VERSION }} · Built with <a href="https://github.com/laravel/ai" class="text-brand hover:text-brand-light transition-colors">laravel/ai</a>
        </p>
    </footer>

</body>
</html>
