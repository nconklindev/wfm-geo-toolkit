<?php

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class IpAddressChecker extends Component
{
    public array $analysisData = [];

    public array $uploadedFile = [];

    public string $currentTab = 'overview';

    public function mount(): void
    {
        if (! session()->has('ipAnalysisData') || ! session()->has('uploadedFile')) {
            session()->flash('error', 'No analysis data found. Please upload a file first.');
            $this->redirect(IpAddressImport::class, navigate: true);

            return;
        }

        $this->analysisData = session('ipAnalysisData');
        $this->uploadedFile = session('uploadedFile');

        // Keep the session data available for page refreshes
        session()->keep(['ipAnalysisData', 'uploadedFile']);
    }

    public function setTab(string $tab): void
    {
        $this->currentTab = $tab;
    }

    public function uploadNewFile(): void
    {
        // Clean up the stored file
        if (isset($this->uploadedFile['path'])) {
            Storage::disk('local')->delete($this->uploadedFile['path']);
        }

        // Clean up the session data
        session()->forget(['ipAnalysisData', 'uploadedFile']);

        // Redirect back to the uploader
        $this->redirect(IpAddressImport::class, navigate: true);
    }

    public function formatBytes($size): string
    {
        return Number::fileSize($size);
    }

    public function formatNumber($number): string
    {
        if ($number >= 1000000) {
            return Number::abbreviate($number, 2);
        }
        if ($number >= 1000) {
            return Number::abbreviate($number, 2);
        }

        return Number::format($number);
    }

    #[Layout('components.layouts.guest')]
    #[Title('IP Address Checker')]
    public function render(): View
    {
        return view('livewire.ip-address-checker');
    }
}
