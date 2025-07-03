<?php

namespace App\Livewire\Tools\HarAnalyzer\Tabs;

use App\Traits\FormatsHarData;
use Livewire\Component;

class Overview extends Component
{
    use FormatsHarData;

    public array $analysisData = [];

    public function mount(array $analysisData): void
    {
        $this->analysisData = $analysisData;
    }

    public function render()
    {
        return view('livewire.tools.har-analyzer.tabs.overview');
    }
}
