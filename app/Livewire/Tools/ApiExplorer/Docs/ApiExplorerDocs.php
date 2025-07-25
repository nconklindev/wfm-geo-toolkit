<?php

namespace App\Livewire\Tools\ApiExplorer\Docs;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class ApiExplorerDocs extends Component
{
    public string $selectedCategory = 'overview';

    #[Layout('components.layouts.guest')]
    #[Title('API Explorer Documentation')]
    public function render()
    {
        return view('livewire.tools.api-explorer.docs.api-explorer-docs');
    }
}
