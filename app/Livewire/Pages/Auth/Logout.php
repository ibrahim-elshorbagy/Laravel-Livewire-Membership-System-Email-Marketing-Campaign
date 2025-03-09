<?php

namespace App\Livewire\Pages\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Logout extends Component
{

    public function logout()
    {
        if (session()->has('impersonated_by')) {
            session()->forget('impersonated_by');
        }

        Auth::logout();

        // Regenerate session after logout
        session()->invalidate();
        session()->regenerateToken();

        return $this->redirect('/', navigate: true);

    }

    public function render()
    {
        return view('livewire.pages.auth.logout');
    }
}
