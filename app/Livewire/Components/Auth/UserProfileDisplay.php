<?php

namespace App\Livewire\Components\Auth;

use Livewire\Component;

class UserProfileDisplay extends Component
{

    public $image_url;
    public $first_name;

    // Listen for the event and call the 'refreshProfile' method
    protected $listeners = ['refresh-user-profile-display' => 'refreshProfile'];

    // Method to handle the event and update the component's properties
    public function refreshProfile($data)
    {
        $this->image_url = $data['image_url'];
        $this->first_name = $data['first_name'];
    }

    public function render()
    {
        return view('livewire.components.auth.user-profile-display');
    }
}
