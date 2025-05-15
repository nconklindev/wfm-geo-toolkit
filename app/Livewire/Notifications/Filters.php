<?php

namespace App\Livewire\Notifications;

use Livewire\Component;

class Filters extends Component
{
    public string $currentFilter = 'all';
    public string $currentStatus = 'all';

    public function render()
    {
        return view('livewire.notifications.filters');
    }
}
