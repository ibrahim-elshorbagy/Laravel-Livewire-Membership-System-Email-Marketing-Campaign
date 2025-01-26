<?php

namespace App\Livewire\Pages\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Logout extends Component
{

    public function logout()
    {
        Auth::logout();
        return $this->redirect('/', navigate: true);

    }
    public function render()
    {
        return view('livewire.pages.auth.logout');
    }
}
