<?php

namespace App\Livewire\Tools\HarAnalyzer;

use App\Concerns\FormatsHarData;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Storage;

class HarAnalyzerResults extends Component
{
    use FormatsHarData;

    public array $analysisData = [];

    public array $uploadedFile = [];

    public string $currentTab = 'overview';

    public function mount(): void
    {
        if (! session()->has('analysisData') || ! session()->has('uploadedFile')) {
            session()->flash('error', 'No analysis data found. Please upload a file first.');
            $this->redirect(HarAnalyzer::class, navigate: true);

            return;
        }

        $this->analysisData = session('analysisData');
        $this->uploadedFile = session('uploadedFile');

        // Keep the session data available for page refreshes
        session()->keep(['analysisData', 'uploadedFile']);
    }

    public function setTab(string $tab): void
    {
        $this->currentTab = $tab;

        // Dispatch event to Alpine.js to sync the tab state
        $this->dispatch('tab-changed', tab: $tab);
    }

    public function analyzeFile(): void
    {
        $this->redirect(HarAnalyzer::class, navigate: true);
    }

    public function removeFile(): void
    {
        // Clean up and redirect - same as startNewAnalysis
        $this->startNewAnalysis();
    }

    public function startNewAnalysis(): void
    {
        // Clean up the stored file when the user is done.
        if (isset($this->uploadedFile['path'])) {
            Storage::disk('local')->delete($this->uploadedFile['path']);
        }

        // Clean up the session data
        session()->forget(['analysisData', 'uploadedFile']);

        // Redirect back to the uploader
        $this->redirect(HarAnalyzer::class, navigate: true);
    }

    #[Layout('components.layouts.guest')]
    #[Title('HAR Analysis Results')]
    public function render()
    {
        return view('livewire.tools.har-analyzer.har-analyzer-results');
    }
}
