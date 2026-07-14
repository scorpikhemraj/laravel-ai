<?php

namespace App\Livewire\Profile;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class PasskeyManager extends Component
{
    public function getPasskeysProperty()
    {
        return Auth::user()->webauthnCredentials()->latest()->get();
    }

    public function deletePasskey($id)
    {
        Auth::user()->webauthnCredentials()->where('id', $id)->delete();
        $this->dispatch('notify', 'Passkey removed.');
    }

    public function getCurrentDeviceIdProperty()
    {
        return \Illuminate\Support\Facades\Cookie::get('webauthn_current_device');
    }

    public function render()
    {
        return view('livewire.profile.passkey-manager');
    }
}
