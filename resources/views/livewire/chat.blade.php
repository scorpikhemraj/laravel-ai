<div x-data="chatWindow(@js(auth()->id()), @entangle('activeChatId').live)"
     @chat-selected.window="subscribeToChannel($event.detail.chatId)"
     @message-sent.window="scrollToBottom()"
     @message-received.window="scrollToBottom()"
     class="flex h-[calc(100vh-4rem)] overflow-hidden bg-surface-950 text-gray-200 font-sans"
>
    <!-- Left Sidebar: Chats List & Actions -->
    <div class="w-80 sm:w-96 flex flex-col border-r border-white/5 bg-surface-900/40 glass-panel">
        
        <!-- Sidebar Header -->
        <div class="p-4 border-b border-white/5 flex items-center justify-between">
            <h2 class="text-lg font-bold tracking-wider text-amber-500 uppercase flex items-center gap-2">
                <span class="inline-block w-2.5 h-2.5 bg-amber-500 status-dot rounded-full shadow-[0_0_8px_#f59e0b]"></span>
                Communications
            </h2>
            <div class="flex items-center gap-2">
                <!-- Create Group Button -->
                <button wire:click="openGroupModal" 
                        class="p-2 rounded-lg bg-surface-800 border border-white/10 hover:border-cyan-500/50 text-gray-400 hover:text-cyan-400 transition duration-200"
                        title="Create Group Chat">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Search / Users Directory -->
        <div class="p-4 border-b border-white/5 space-y-3">
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </span>
                <input type="text" 
                       wire:model.live.debounce.300ms="searchQuery" 
                       class="w-full bg-surface-950 border border-white/10 rounded-lg pl-9 pr-4 py-2 text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500/30 transition duration-200" 
                       placeholder="Search users or channels...">
            </div>

            <!-- Search Results Dropdown/List -->
            @if(trim($searchQuery) !== '')
                <div class="max-h-60 overflow-y-auto custom-scrollbar border border-white/10 bg-surface-950 rounded-lg p-2 space-y-1">
                    <div class="text-[10px] uppercase tracking-wider font-semibold text-gray-500 px-2 py-1">Users Directory</div>
                    @forelse($users as $user)
                        <button wire:click="startDirectChat({{ $user->id }})" 
                                class="w-full text-left flex items-center gap-3 p-2 rounded-lg hover:bg-surface-800/80 transition duration-150 group">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-cyan-600 to-blue-800 flex items-center justify-center font-bold text-xs uppercase text-cyan-200 shadow-md">
                                {{ substr($user->name, 0, 2) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-300 group-hover:text-cyan-400 transition">{{ $user->name }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ $user->email }}</p>
                            </div>
                        </button>
                    @empty
                        <div class="text-xs text-gray-500 p-2 text-center">No users found.</div>
                    @endforelse
                </div>
            @endif
        </div>

        <!-- Chats List -->
        <div class="flex-1 overflow-y-auto custom-scrollbar p-2 space-y-1">
            @forelse($chats as $chatItem)
                <button wire:click="selectChat({{ $chatItem->id }})" 
                        class="w-full text-left flex items-center gap-3 p-3 rounded-lg transition duration-200 @if($activeChatId == $chatItem->id) bg-amber-500/10 border border-amber-500/20 glow-amber @else border border-transparent hover:bg-surface-800/50 @endif">
                    
                    <!-- Chat Avatar -->
                    <div class="relative flex-shrink-0">
                        @if($chatItem->is_group)
                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-600 to-indigo-800 flex items-center justify-center font-bold text-sm text-purple-200 shadow-md">
                                GP
                            </div>
                        @else
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-cyan-600 to-blue-800 flex items-center justify-center font-bold text-sm uppercase text-cyan-200 shadow-md">
                                {{ substr($chatItem->displayNameFor(auth()->user()), 0, 2) }}
                            </div>
                            <!-- Active Status Indicator (Mock/Static for direct chats) -->
                            <span class="absolute bottom-0 right-0 block h-2.5 w-2.5 rounded-full bg-emerald-500 ring-2 ring-surface-900 status-dot"></span>
                        @endif
                    </div>

                    <!-- Chat Name & Last Message Preview -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-baseline justify-between mb-1">
                            <h3 class="text-sm font-semibold truncate @if($activeChatId == $chatItem->id) text-amber-400 @else text-gray-300 @endif">
                                {{ $chatItem->displayNameFor(auth()->user()) }}
                            </h3>
                            <span class="text-[10px] text-gray-500">
                                {{ $chatItem->updated_at->diffForHumans(null, true) }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-400 truncate">
                            @if($chatItem->latestMessage)
                                <span class="font-medium text-gray-300">{{ $chatItem->latestMessage->user->name }}:</span> 
                                @if($chatItem->latestMessage->is_attachment)
                                    📎 Attached File
                                @else
                                    {{ $chatItem->latestMessage->message }}
                                @endif
                            @else
                                <span class="italic text-gray-500">No messages yet</span>
                            @endif
                        </p>
                    </div>
                </button>
            @empty
                <div class="flex flex-col items-center justify-center h-40 text-gray-500 text-sm">
                    <svg class="w-8 h-8 mb-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    No active communications
                </div>
            @endforelse
        </div>
    </div>

    <!-- Right Area: Chat Conversation window -->
    <div class="flex-1 flex flex-col bg-surface-950 relative">
        @if($activeChat)
            <!-- Chat Window Header -->
            <div class="h-16 border-b border-white/5 bg-surface-900/20 px-6 flex items-center justify-between glass-panel z-10">
                <div class="flex items-center gap-3">
                    @if($activeChat->is_group)
                        <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-purple-600 to-indigo-800 flex items-center justify-center font-bold text-sm text-purple-200 shadow-md">
                            GP
                        </div>
                    @else
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-cyan-600 to-blue-800 flex items-center justify-center font-bold text-xs uppercase text-cyan-200 shadow-md">
                            {{ substr($activeChat->displayNameFor(auth()->user()), 0, 2) }}
                        </div>
                    @endif
                    
                    <div>
                        <h2 class="text-sm font-semibold text-gray-200">{{ $activeChat->displayNameFor(auth()->user()) }}</h2>
                        <p class="text-xs text-gray-500">
                            @if($activeChat->is_group)
                                Group • {{ $activeChat->users->count() }} members
                            @else
                                Direct Encryption Channel
                            @endif
                        </p>
                    </div>
                </div>

                <!-- Channel Actions -->
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-1.5 text-xs text-emerald-400/90 font-medium">
                        <span class="inline-block w-2 h-2 bg-emerald-500 rounded-full status-dot"></span>
                        Reverb Connected
                    </div>
                </div>
            </div>

            <!-- Messages List Feed -->
            <div x-ref="messagesContainer" class="flex-1 overflow-y-auto custom-scrollbar p-6 space-y-4">
                @php
                    $lastDate = null;
                @endphp
                @forelse($activeChat->messages as $message)
                    @php
                        $messageDate = $message->created_at->format('F d, Y');
                    @endphp
                    
                    @if($lastDate !== $messageDate)
                        <div class="flex justify-center my-4">
                            <span class="px-3 py-1 bg-surface-800/80 border border-white/5 rounded-full text-[10px] font-semibold text-gray-500 uppercase tracking-widest">
                                {{ $messageDate }}
                            </span>
                        </div>
                        @php
                            $lastDate = $messageDate;
                        @endphp
                    @endif

                    <div class="flex gap-3 @if($message->user_id == auth()->id()) justify-end @endif">
                        <!-- Sender Avatar (for others in groups) -->
                        @if($message->user_id != auth()->id() && $activeChat->is_group)
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-cyan-600 to-blue-800 flex items-center justify-center font-bold text-[10px] uppercase text-cyan-200 shadow-md flex-shrink-0 self-end mb-1">
                                {{ substr($message->user->name, 0, 2) }}
                            </div>
                        @endif

                        <!-- Message bubble wrapper -->
                        <div class="max-w-[70%] flex flex-col @if($message->user_id == auth()->id()) items-end @endif">
                            <!-- Sender name label -->
                            @if($message->user_id != auth()->id() && $activeChat->is_group)
                                <span class="text-[10px] text-gray-500 ml-1 mb-1 font-semibold">{{ $message->user->name }}</span>
                            @endif

                            <!-- Bubble -->
                            <div class="p-3.5 rounded-2xl border transition duration-200 @if($message->user_id == auth()->id()) bg-gradient-to-br from-amber-500/10 to-amber-600/5 border-amber-500/20 rounded-tr-none text-gray-200 shadow-[0_4px_12px_-2px_rgba(245,158,11,0.03)] @else bg-surface-900/60 border-white/5 rounded-tl-none text-gray-300 @endif">
                                
                                <!-- Text content -->
                                @if($message->message)
                                    <p class="text-sm whitespace-pre-wrap leading-relaxed">{{ $message->message }}</p>
                                @endif

                                <!-- Attachment display -->
                                @if($message->is_attachment)
                                    <div class="@if($message->message) mt-3 pt-3 border-t border-white/5 @endif">
                                        @if(str_starts_with($message->attachment_mime, 'image/'))
                                            <div class="relative group rounded-lg overflow-hidden border border-white/10 max-w-xs sm:max-w-md bg-surface-950">
                                                <img src="{{ $message->attachment_url }}" alt="{{ $message->attachment_name }}" class="max-h-60 w-auto object-cover hover:scale-[1.02] transition duration-300">
                                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-2">
                                                    <a href="{{ $message->attachment_url }}" download="{{ $message->attachment_name }}" class="text-xs text-white bg-amber-500/90 px-2 py-1 rounded hover:bg-amber-500 flex items-center gap-1 transition font-semibold">
                                                        Download
                                                    </a>
                                                </div>
                                            </div>
                                        @else
                                            <div class="flex items-center gap-3 p-2.5 rounded-lg bg-surface-950 border border-white/5 max-w-xs sm:max-w-md">
                                                <div class="p-2 rounded bg-surface-850 border border-white/10 text-cyan-400">
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-xs font-semibold text-gray-300 truncate" title="{{ $message->attachment_name }}">{{ $message->attachment_name }}</p>
                                                    <p class="text-[10px] text-gray-500">{{ number_format($message->attachment_size / 1024, 1) }} KB</p>
                                                </div>
                                                <a href="{{ $message->attachment_url }}" download="{{ $message->attachment_name }}" class="p-2 rounded bg-surface-800 text-gray-400 hover:text-cyan-400 transition border border-white/10">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                    </svg>
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <!-- Message timestamp -->
                            <span class="text-[9px] text-gray-500 mt-1 px-1">
                                {{ $message->created_at->format('H:i') }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center h-full text-gray-500">
                        <div class="p-4 rounded-full bg-surface-900 border border-white/5 mb-3 text-cyan-500/80 shadow-inner">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                        </div>
                        <p class="text-sm font-semibold tracking-wider uppercase text-cyan-400">Secure Uplink Established</p>
                        <p class="text-xs text-gray-600 mt-1">Send a message to begin data streaming</p>
                    </div>
                @endforelse
            </div>

            <!-- Message Input Panel -->
            <div class="p-4 border-t border-white/5 bg-surface-900/10 glass-panel">
                <form wire:submit.prevent="sendMessage" class="space-y-3">
                    
                    <!-- File attachment preview indicator -->
                    @if($attachment)
                        <div class="flex items-center justify-between p-2 rounded-lg bg-amber-500/5 border border-amber-500/20 max-w-sm">
                            <div class="flex items-center gap-2 min-w-0">
                                <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                </svg>
                                <span class="text-xs text-gray-300 truncate font-semibold">{{ $attachment->getClientOriginalName() }}</span>
                            </div>
                            <button type="button" wire:click="$set('attachment', null)" class="text-gray-500 hover:text-rose-400 transition ml-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    @endif

                    <div class="flex items-end gap-3">
                        <!-- Upload Paperclip -->
                        <div class="flex-shrink-0">
                            <label class="cursor-pointer flex items-center justify-center p-3 rounded-xl bg-surface-850 hover:bg-surface-800 border border-white/10 hover:border-cyan-500/30 text-gray-400 hover:text-cyan-400 transition duration-200 shadow-md">
                                <input type="file" wire:model="attachment" class="hidden">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                </svg>
                            </label>
                        </div>

                        <!-- Message Input Field -->
                        <div class="flex-1 relative">
                            <textarea wire:model="messageText" 
                                      wire:keydown.enter.prevent="sendMessage"
                                      rows="1" 
                                      class="w-full bg-surface-950 border border-white/10 rounded-xl px-4 py-3 text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500/30 resize-none max-h-36 custom-scrollbar transition duration-200" 
                                      placeholder="Type encryption payload... (Press Enter to Send)"></textarea>
                        </div>

                        <!-- Send Button -->
                        <button type="submit" 
                                class="flex-shrink-0 flex items-center justify-center p-3 rounded-xl bg-amber-500 hover:bg-amber-600 text-surface-950 transition duration-200 shadow-[0_0_12px_rgba(245,158,11,0.2)] hover:shadow-[0_0_20px_rgba(245,158,11,0.35)] font-bold">
                            <svg class="w-5 h-5 transform rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
        @else
            <!-- Dashboard Welcome / Intro State -->
            <div class="flex-1 flex flex-col items-center justify-center p-6 text-center scan-line">
                <div class="max-w-md space-y-6">
                    <div class="inline-flex p-5 rounded-3xl bg-surface-900 border hud-border text-amber-500 shadow-inner glow-amber">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>

                    <div class="space-y-2">
                        <h1 class="text-2xl font-bold tracking-widest text-amber-500 uppercase">Secure Communication Node</h1>
                        <p class="text-sm text-gray-400">Select an active encryption thread from the communications panel, or lookup direct user identifiers to initialize a secure peer-to-peer relay.</p>
                    </div>

                    <div class="flex items-center justify-center gap-4 text-xs font-semibold text-gray-500 uppercase tracking-widest border-t border-white/5 pt-6">
                        <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-cyan-500 status-dot"></span> End-to-End Relay</span>
                        <span class="text-white/10">•</span>
                        <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-amber-500 status-dot"></span> Websocket Node</span>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Group Creation Modal overlay (Styled beautifully) -->
    @if($groupModalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
            <div class="w-full max-w-md bg-surface-900 border hud-border-accent rounded-2xl shadow-2xl glow-cyan overflow-hidden transform transition duration-300 scale-100">
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-white/5 flex items-center justify-between bg-surface-950/40">
                    <h3 class="text-base font-bold uppercase tracking-wider text-cyan-400 flex items-center gap-2">
                        <span class="w-2 h-2 bg-cyan-400 rounded-full status-dot"></span>
                        Assemble Group Channel
                    </h3>
                    <button wire:click="$set('groupModalOpen', false)" class="text-gray-500 hover:text-rose-400 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Modal Body Form -->
                <div class="p-6 space-y-4">
                    <!-- Group Name Input -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-widest text-cyan-400/80 mb-2">Channel Identifier Name</label>
                        <input type="text" 
                               wire:model="newGroupName" 
                               class="w-full bg-surface-950 border border-white/10 rounded-lg px-3 py-2.5 text-sm text-gray-200 focus:outline-none focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400/30 transition duration-200" 
                               placeholder="e.g. Sales Team Alpha">
                        @error('newGroupName') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- User Selection Checkbox List -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-widest text-cyan-400/80 mb-2">Authorize Channel Members</label>
                        <div class="max-h-48 overflow-y-auto custom-scrollbar border border-white/10 bg-surface-950 rounded-lg p-2 space-y-1">
                            @foreach($users as $user)
                                <label class="flex items-center justify-between p-2 rounded hover:bg-surface-800/60 transition cursor-pointer select-none">
                                    <div class="flex items-center gap-3">
                                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-cyan-600 to-blue-800 flex items-center justify-center font-bold text-[10px] uppercase text-cyan-200">
                                            {{ substr($user->name, 0, 2) }}
                                        </div>
                                        <span class="text-sm font-medium text-gray-300">{{ $user->name }}</span>
                                    </div>
                                    <input type="checkbox" 
                                           value="{{ $user->id }}" 
                                           wire:model="selectedUsersForGroup" 
                                           class="rounded border-white/10 text-cyan-500 bg-surface-950 focus:ring-offset-0 focus:ring-cyan-500/30">
                                </label>
                            @endforeach
                        </div>
                        @error('selectedUsersForGroup') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 border-t border-white/5 flex items-center justify-end gap-3 bg-surface-950/40">
                    <button wire:click="$set('groupModalOpen', false)" 
                            class="px-4 py-2 text-xs font-bold uppercase tracking-wider text-gray-400 hover:text-white transition rounded-lg border border-white/10 hover:border-white/20">
                        Cancel
                    </button>
                    <button wire:click="createGroupChat" 
                            class="px-4 py-2 text-xs font-bold uppercase tracking-wider bg-cyan-500 hover:bg-cyan-600 text-surface-950 rounded-lg transition shadow-[0_0_12px_rgba(6,182,212,0.25)] hover:shadow-[0_0_20px_rgba(6,182,212,0.4)]">
                        Launch Channel
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Inline Alpine script logic -->
    <script>
        function chatWindow(authUserId, activeChatId) {
            return {
                authUserId: authUserId,
                activeChatId: activeChatId,
                channelName: null,
                init() {
                    if (this.activeChatId) {
                        this.subscribeToChannel(this.activeChatId);
                    }
                    this.scrollToBottom();
                },
                subscribeToChannel(chatId) {
                    if (this.channelName) {
                        Echo.leave(this.channelName);
                        this.channelName = null;
                    }
                    if (!chatId) return;
                    this.channelName = 'chat.' + chatId;
                    
                    Echo.private(this.channelName)
                        .listen('MessageSent', (e) => {
                            if (e.user_id !== this.authUserId) {
                                this.$wire.appendBroadcastedMessage(e);
                            }
                        });
                    this.scrollToBottom();
                },
                scrollToBottom() {
                    this.$nextTick(() => {
                        const container = this.$refs.messagesContainer;
                        if (container) {
                            container.scrollTop = container.scrollHeight;
                        }
                    });
                }
            }
        }
    </script>
</div>
