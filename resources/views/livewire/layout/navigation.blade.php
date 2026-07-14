<?php

use App\Http\Livewire\Actions\Logout;

$logout = function (Logout $logout) {
    $logout();

    $this->redirect('/', navigate: true);
};

?>

<nav x-data="{ open: false }" class="bg-surface-800/80 border-b border-white/5 sticky top-0 z-50 backdrop-blur-xl">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">

            <div class="flex items-center gap-8">
                <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-2.5 shrink-0">
                    <div class="w-8 h-8 rounded-lg bg-surface-700 border border-brand/20 flex items-center justify-center">
                        <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <span class="font-display font-semibold text-white text-[15px] tracking-tight">Laravel AI</span>
                </a>

                <div class="hidden sm:flex items-center gap-1">
                    <a href="{{ route('dashboard') }}" wire:navigate
                        class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-150
                            {{ request()->routeIs('dashboard') ? 'bg-brand/10 text-brand' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Dashboard
                    </a>
                    <a href="{{ route('ai.playground') }}" wire:navigate
                        class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-150
                            {{ request()->routeIs('ai.playground') ? 'bg-brand/10 text-brand' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        AI Playground
                    </a>
                    <a href="{{ route('chat') }}" wire:navigate
                        class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-150
                            {{ request()->routeIs('chat') ? 'bg-brand/10 text-brand' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        Secure Chat
                    </a>
                    <a href="{{ route('calendar') }}" wire:navigate
                        class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-150
                            {{ request()->routeIs('calendar') ? 'bg-brand/10 text-brand' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Calendar
                    </a>
                    <a href="{{ route('leads.index') }}" wire:navigate
                        class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-150
                            {{ request()->routeIs('leads.index') ? 'bg-brand/10 text-brand' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        Leads
                    </a>
                    <a href="{{ route('posts') }}" wire:navigate
                        class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-150
                            {{ request()->routeIs('posts') ? 'bg-brand/10 text-brand' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Posts
                    </a>
                </div>
            </div>

            <div class="hidden sm:flex items-center gap-3">
                @auth
                    <x-dropdown align="right" width="52">
                        <x-slot name="trigger">
                            <button class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl border border-white/10 bg-surface-700 hover:bg-surface-600 transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-brand/50 focus:ring-offset-1 focus:ring-offset-surface-900">
                                <div class="w-7 h-7 rounded-full bg-brand/20 border border-brand/30 flex items-center justify-center text-brand text-xs font-semibold shrink-0">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                                <div x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"
                                    class="text-sm font-medium text-gray-300 max-w-[120px] truncate"></div>
                                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="px-4 py-3 border-b border-white/5">
                                <p class="text-xs text-gray-500">Signed in as</p>
                                <p class="text-sm font-medium text-gray-300 truncate">{{ auth()->user()->email }}</p>
                            </div>
                            <x-dropdown-link :href="route('profile')" wire:navigate>
                                <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Profile
                            </x-dropdown-link>
                            <div class="border-t border-white/5 mt-1 pt-1">
                                <button wire:click="logout" class="w-full text-start">
                                    <x-dropdown-link class="text-red-400 hover:text-red-300 hover:bg-red-500/10">
                                        <svg class="w-4 h-4 mr-2 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                        Log Out
                                    </x-dropdown-link>
                                </button>
                            </div>
                        </x-slot>
                    </x-dropdown>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-medium text-gray-400 hover:text-white transition duration-150">Log in</a>
                @endauth
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = !open" class="inline-flex items-center justify-center p-2 rounded-lg text-gray-400 hover:text-white hover:bg-white/5 focus:outline-none transition duration-150">
                    <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': !open}" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path :class="{'hidden': !open, 'inline-flex': open}" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': !open}" class="hidden sm:hidden border-t border-white/5 bg-surface-800">
        <div class="px-4 pt-3 pb-2 space-y-1">
            <a href="{{ route('dashboard') }}" wire:navigate
                class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                    {{ request()->routeIs('dashboard') ? 'bg-brand/10 text-brand' : 'text-gray-400 hover:bg-white/5' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Dashboard
            </a>
            <a href="{{ route('ai.playground') }}" wire:navigate
                class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                    {{ request()->routeIs('ai.playground') ? 'bg-brand/10 text-brand' : 'text-gray-400 hover:bg-white/5' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                AI Playground
            </a>
            <a href="{{ route('chat') }}" wire:navigate
                class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                    {{ request()->routeIs('chat') ? 'bg-brand/10 text-brand' : 'text-gray-400 hover:bg-white/5' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                Secure Chat
            </a>
            <a href="{{ route('calendar') }}" wire:navigate
                class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                    {{ request()->routeIs('calendar') ? 'bg-brand/10 text-brand' : 'text-gray-400 hover:bg-white/5' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Calendar
            </a>
            <a href="{{ route('leads.index') }}" wire:navigate
                class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                    {{ request()->routeIs('leads.index') ? 'bg-brand/10 text-brand' : 'text-gray-400 hover:bg-white/5' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                Leads
            </a>
            <a href="{{ route('posts') }}" wire:navigate
                class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                    {{ request()->routeIs('posts') ? 'bg-brand/10 text-brand' : 'text-gray-400 hover:bg-white/5' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Posts
            </a>
        </div>

        @auth
            <div class="px-4 pt-3 pb-4 border-t border-white/5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-9 h-9 rounded-full bg-brand/20 border border-brand/30 flex items-center justify-center text-brand text-sm font-semibold">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-300" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>
                        <div class="text-xs text-gray-500">{{ auth()->user()->email }}</div>
                    </div>
                </div>
                <div class="space-y-1">
                    <x-responsive-nav-link :href="route('profile')" wire:navigate class="text-gray-400 hover:text-white hover:bg-white/5">
                        Profile
                    </x-responsive-nav-link>
                    <button wire:click="logout" class="w-full text-start">
                        <x-responsive-nav-link class="text-red-400 hover:text-red-300 hover:bg-red-500/10">
                            Log Out
                        </x-responsive-nav-link>
                    </button>
                </div>
            </div>
        @else
            <div class="px-4 pt-3 pb-4 border-t border-white/5">
                <a href="{{ route('login') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-400 hover:text-white hover:bg-white/5">Log in</a>
            </div>
        @endauth
    </div>
</nav>
