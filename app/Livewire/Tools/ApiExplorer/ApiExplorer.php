<?php

namespace App\Livewire\Tools\ApiExplorer;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class ApiExplorer extends Component
{
    #[Layout('components.layouts.guest')]
    #[Title('API Explorer')]
    public function render()
    {
        return view('livewire.tools.api-explorer.api-explorer');
    }
}
