<section class="bg-white/40 backdrop-blur-md rounded-2xl border border-white/50 p-6 shadow-sm">
    <header class="mb-8">
        <div class="flex items-center gap-3 mb-2">
            <div class="p-2 bg-indigo-500/10 rounded-lg">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4" />
                </svg>
            </div>
            <h2 class="text-xl font-semibold text-gray-900">
                {{ __('Biometric & Security Keys') }}
            </h2>
        </div>

        <p class="text-sm text-gray-600 ml-11">
            {{ __("Secure your account using your device's fingerprint sensor, Face ID, or a hardware security key. Passkeys are more secure than passwords.") }}
        </p>
    </header>

    <div class="space-y-4">
        @forelse ($this->passkeys as $passkey)
            <div class="group flex items-center justify-between p-4 bg-white/60 hover:bg-white/80 transition-all rounded-xl border border-gray-100/50 shadow-sm hover:shadow-md">
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <div class="p-3 bg-indigo-50 rounded-xl text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        @if($passkey->last_used_at && $passkey->last_used_at->gt(now()->subDays(7)))
                            <span class="absolute -top-1 -right-1 w-3 h-3 bg-green-500 border-2 border-white rounded-full"></span>
                        @endif
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <p class="font-semibold text-gray-900">{{ $passkey->alias ?: $passkey->name ?: __('Unnamed Device') }}</p>
                            @if((string)$this->currentDeviceId === (string)$passkey->id)
                                <span class="px-2 py-0.5 text-[10px] font-medium tracking-wide text-indigo-700 bg-indigo-100 rounded-full border border-indigo-200">
                                    {{ __('Current Device') }}
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 flex items-center gap-2 mt-0.5">
                            <span class="flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                {{ $passkey->created_at->format('M j, Y') }}
                            </span>
                            <span class="text-gray-300">•</span>
                            <span class="flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                {{ $passkey->last_used_at?->diffForHumans() ?? __('Never used') }}
                            </span>
                        </p>
                    </div>
                </div>
                <button 
                    wire:click="deletePasskey('{{ $passkey->id }}')" 
                    wire:confirm="{{ __('Are you sure you want to remove this security key?') }}"
                    class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all"
                    title="{{ __('Remove Key') }}"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </div>
        @empty
            <div class="text-center py-10 bg-gray-50/50 rounded-2xl border border-dashed border-gray-200">
                <div class="inline-flex p-3 bg-gray-100 rounded-full text-gray-400 mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                </div>
                <p class="text-sm text-gray-500">{{ __('No passkeys registered yet.') }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ __('Add your first device below to start using biometrics.') }}</p>
            </div>
        @endforelse
    </div>

    <div class="mt-10 pt-8 border-t border-gray-100/50">
        <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center gap-2">
            <span class="w-1.5 h-6 bg-indigo-600 rounded-full"></span>
            {{ __('Add a New Passkey') }}
        </h3>
        
        <div class="bg-indigo-50/30 rounded-2xl p-6 border border-indigo-100/50">
            <div class="flex flex-col md:flex-row items-stretch md:items-end gap-4">
                <div class="flex-1">
                    <label for="new-passkey-name" class="block text-sm font-medium text-gray-700 mb-1 ml-1">{{ __('Key Name') }}</label>
                    <x-text-input 
                        id="new-passkey-name" 
                        placeholder="{{ __('e.g. My Laptop, iPhone FaceID') }}" 
                        class="block w-full bg-white/80 border-gray-200 focus:ring-indigo-500 focus:border-indigo-500 rounded-xl"
                    />
                </div>
                <x-primary-button 
                    type="button"
                    class="h-[42px] px-6 bg-indigo-600 hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all transform active:scale-95"
                    onclick="const nameInput = document.getElementById('new-passkey-name'); window.registerPasskey(nameInput.value || 'New Passkey').then(success => { if(success) { nameInput.value = ''; $wire.$refresh(); } })"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    {{ __('Register Device') }}
                </x-primary-button>
            </div>
            <p class="mt-4 text-xs text-gray-500 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ __('You will be prompted to use your fingerprint, face, or security key to complete setup.') }}
            </p>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const nameInput = document.getElementById('new-passkey-name');
        if (!nameInput) return;

        let deviceName = 'My Device';
        const ua = navigator.userAgent;

        if (ua.includes('Windows')) deviceName = 'Windows Laptop';
        else if (ua.includes('Macintosh')) deviceName = 'MacBook';
        else if (ua.includes('iPhone')) deviceName = 'iPhone';
        else if (ua.includes('iPad')) deviceName = 'iPad';
        else if (ua.includes('Android')) deviceName = 'Android Device';
        else if (ua.includes('Linux')) deviceName = 'Linux Desktop';

        nameInput.value = deviceName;
    });
</script>
