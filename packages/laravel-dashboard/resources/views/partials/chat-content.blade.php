<div class="flex flex-col h-full overflow-hidden bg-slate-950/85 backdrop-blur-md">
    <!-- Header -->
    <div class="p-4 border-b border-slate-900 flex items-center justify-between bg-slate-900/40 shrink-0">
        <div class="flex items-center gap-2">
            <div class="h-8 w-8 rounded-lg bg-indigo-600/20 border border-indigo-500/30 flex items-center justify-center text-indigo-400 shadow-md shadow-indigo-500/10">
                <i class="fa-solid fa-robot text-sm"></i>
            </div>
            <div>
                <h4 class="font-bold text-xs text-slate-100">Analytics Assistant</h4>
                <div class="flex items-center gap-1">
                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-[9px] text-slate-400">Playground Agent Online</span>
                </div>
            </div>
        </div>

        <!-- Controls -->
        <div class="flex items-center gap-2">
            <!-- Placement Selector -->
            <div class="flex items-center gap-1 bg-slate-950/60 border border-slate-800/60 rounded-md px-1.5 py-0.5 text-[9px]">
                <i class="fa-solid fa-arrows-to-dot text-slate-500 text-[10px]"></i>
                <select 
                    x-model="chatPlacement" 
                    class="bg-transparent border-none text-slate-300 outline-none cursor-pointer py-0 pl-0.5 pr-4 text-[9px] font-semibold focus:ring-0 focus:outline-none"
                    style="background-position: right 0.25rem center; background-size: 0.5em; padding-right: 1rem;"
                >
                    <option value="overlay-bottom-right" class="bg-slate-950 text-slate-300 text-xs">Overlay Bottom-R</option>
                    <option value="overlay-bottom-left" class="bg-slate-950 text-slate-300 text-xs">Overlay Bottom-L</option>
                    <option value="overlay-top-right" class="bg-slate-950 text-slate-300 text-xs">Overlay Top-R</option>
                    <option value="overlay-top-left" class="bg-slate-950 text-slate-300 text-xs">Overlay Top-L</option>
                    <option value="side-panel-right" class="bg-slate-950 text-slate-300 text-xs">Panel Right</option>
                    <option value="side-panel-left" class="bg-slate-950 text-slate-300 text-xs">Panel Left</option>
                </select>
            </div>

            <!-- Clear -->
            <button 
                type="button" 
                @click="clearChat()" 
                class="p-1 rounded text-slate-500 hover:text-slate-300 hover:bg-slate-900/60 transition-colors"
                title="Clear conversation"
            >
                <i class="fa-solid fa-trash-can text-xs"></i>
            </button>

            <!-- Close -->
            <button 
                type="button" 
                @click="isChatOpen = false" 
                class="p-1 rounded text-slate-500 hover:text-slate-300 hover:bg-slate-900/60 transition-colors"
                title="Close chat"
            >
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>
    </div>

    <!-- Messages -->
    <div 
        x-ref="chatMessages"
        class="flex-1 overflow-y-auto p-4 space-y-4 scroll-smooth"
    >
        <!-- Empty State -->
        <template x-if="chatMessages.length === 0">
            <div class="h-full flex flex-col items-center justify-center text-center p-4">
                <div class="w-12 h-12 rounded-xl bg-indigo-600/10 border border-indigo-500/20 flex items-center justify-center mb-3">
                    <i class="fa-solid fa-comment-dots text-indigo-400 text-lg"></i>
                </div>
                <h5 class="text-xs font-bold text-slate-200 mb-1">Ask anything about your data</h5>
                <p class="text-[10px] text-slate-400 max-w-[200px] mb-4">I can query the databases, analyze trends, or help configure filters.</p>
                
                <!-- Quick Prompts -->
                <div class="flex flex-col gap-2 w-full max-w-[220px]">
                    <button 
                        type="button" 
                        @click="chatInput = 'Summarize current leads by status'; sendChat()" 
                        class="text-[10px] text-left px-3 py-1.5 rounded-lg bg-slate-900/60 border border-slate-800/80 text-slate-300 hover:bg-slate-900 hover:border-indigo-500/30 hover:text-white transition-all"
                    >
                        📊 Summarize leads by status
                    </button>
                    <button 
                        type="button" 
                        @click="chatInput = 'What are the top open opportunities?'; sendChat()" 
                        class="text-[10px] text-left px-3 py-1.5 rounded-lg bg-slate-900/60 border border-slate-800/80 text-slate-300 hover:bg-slate-900 hover:border-indigo-500/30 hover:text-white transition-all"
                    >
                        💼 What are the top opportunities?
                    </button>
                    <button 
                        type="button" 
                        @click="chatInput = 'How many users are registered?'; sendChat()" 
                        class="text-[10px] text-left px-3 py-1.5 rounded-lg bg-slate-900/60 border border-slate-800/80 text-slate-300 hover:bg-slate-900 hover:border-indigo-500/30 hover:text-white transition-all"
                    >
                        👥 Show registered users count
                    </button>
                </div>
            </div>
        </template>

        <!-- Message Loop -->
        <template x-for="(msg, idx) in chatMessages" :key="idx">
            <div 
                class="flex flex-col gap-1 w-full"
                :class="msg.role === 'user' ? 'items-end' : 'items-start'"
            >
                <!-- Role indicator -->
                <span class="text-[8px] font-semibold uppercase tracking-wider text-slate-500" x-text="msg.role === 'user' ? 'You' : 'Assistant'"></span>
                
                <!-- Message bubble -->
                <div 
                    class="px-3 py-2 rounded-xl text-xs max-w-[85%] leading-relaxed"
                    :class="msg.role === 'user' 
                        ? 'bg-indigo-600/20 border border-indigo-500/30 text-indigo-100 rounded-tr-none' 
                        : (msg.role === 'system' 
                            ? 'bg-red-950/20 border border-red-500/20 text-red-300' 
                            : 'bg-slate-900/60 border border-slate-800/80 text-slate-200 rounded-tl-none')"
                >
                    <div class="prose prose-sm max-w-none text-slate-200" x-html="renderMarkdown(msg.content)"></div>
                </div>
            </div>
        </template>

        <!-- Loading message -->
        <template x-if="isChatLoading">
            <div class="flex flex-col gap-1 items-start w-full">
                <span class="text-[8px] font-semibold uppercase tracking-wider text-slate-500">Assistant</span>
                <div class="px-3 py-2.5 rounded-xl rounded-tl-none bg-slate-900/60 border border-slate-800/80 text-slate-400 text-xs flex items-center gap-1.5 shadow-sm">
                    <span class="w-1.5 h-1.5 bg-indigo-400 rounded-full animate-bounce" style="animation-delay: 0ms"></span>
                    <span class="w-1.5 h-1.5 bg-indigo-400 rounded-full animate-bounce" style="animation-delay: 150ms"></span>
                    <span class="w-1.5 h-1.5 bg-indigo-400 rounded-full animate-bounce" style="animation-delay: 300ms"></span>
                </div>
            </div>
        </template>
    </div>

    <!-- Input Footer -->
    <div class="p-3 border-t border-slate-900 bg-slate-900/20 shrink-0">
        <form @submit.prevent="sendChat()" class="flex items-center gap-2">
            <input 
                type="text" 
                x-model="chatInput" 
                placeholder="Ask about leads, opportunities..." 
                class="flex-1 bg-slate-950 border border-slate-850 rounded-lg px-3 py-1.5 text-xs text-slate-200 outline-none focus:border-indigo-500/50 transition-colors"
                :disabled="isChatLoading"
            >
            <button 
                type="submit" 
                class="h-8 w-8 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white flex items-center justify-center transition-colors shrink-0 glow-indigo"
                :disabled="isChatLoading"
            >
                <i class="fa-solid fa-paper-plane text-xs"></i>
            </button>
        </form>
    </div>
</div>
