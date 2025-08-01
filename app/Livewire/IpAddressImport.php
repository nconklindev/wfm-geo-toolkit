<?php

namespace App\Livewire;

use App\IpAddressRange;
use App\Services\IpValidationService;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class IpAddressImport extends Component
{
    use WithFileUploads;

    #[Validate('required|file|mimes:json|max:10240')]
    public $file;

    public bool $uploading = false;

    public ?array $uploadedFile = null;

    public function uploadFile(): void
    {
        Log::info('IP Address import upload started', ['file_size' => $this->file?->getSize()]);

        $this->validate();

        try {
            Log::info('Starting file storage');

            $path = $this->file->store('ip-imports', 'local');

            Log::info('File stored successfully', ['path' => $path]);

            $this->uploadedFile = [
                'name' => $this->file->getClientOriginalName(),
                'size' => $this->file->getSize(),
                'path' => $path,
                'uploaded_at' => now(),
            ];

            $this->file = null;
            session()->flash('success', 'File uploaded successfully. Ready for analysis.');

            Log::info('Upload completed successfully');

        } catch (Exception $e) {
            Log::error('Upload failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            session()->flash('error', 'Failed to upload file: '.$e->getMessage());
        }
    }

    public function analyzeFile(): void
    {
        if (! $this->uploadedFile || ! Storage::disk('local')->exists($this->uploadedFile['path'])) {
            session()->flash('error', 'File not found. Please upload it again.');
            $this->reset('uploadedFile');

            return;
        }

        try {
            $filePath = Storage::disk('local')->path($this->uploadedFile['path']);
            $content = file_get_contents($filePath);
            $jsonData = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON format: '.json_last_error_msg());
            }

            if (! is_array($jsonData)) {
                throw new Exception('JSON must contain an array of IP address entries.');
            }

            // Create IpAddressRange objects from the JSON data
            $ipRanges = $this->createIpRangeObjects($jsonData);
            $validationService = new IpValidationService;

            // Use the service's validateMultipleRanges method
            $validationResults = $validationService->validateMultipleRanges($ipRanges);
            $summary = $validationService->generateSummary($validationResults);

            // Convert IpAddressRange objects to arrays for session storage
            $analysisData = [
                'original_data' => $jsonData,
                'ip_ranges' => array_map(fn ($ipRange) => $ipRange->toArray(), $ipRanges),
                'validation_results' => array_map(function ($result) {
                    return [
                        'ip_range' => $result['ip_range']->toArray(),
                        'issues' => $result['issues'],
                        'status' => $result['status'],
                    ];
                }, $validationResults),
                'summary' => $summary,
            ];

            // Store data in session for results page
            session()->put('ipAnalysisData', $analysisData);
            session()->put('uploadedFile', $this->uploadedFile);

            $this->redirect(IpAddressChecker::class, navigate: true);

        } catch (Exception $e) {
            session()->flash('error', 'Failed to analyze file: '.$e->getMessage());
            $this->removeFile();
        }
    }

    protected function createIpRangeObjects(array $jsonData): array
    {
        $ipRanges = [];

        foreach ($jsonData as $index => $entry) {
            $ipRanges[] = IpAddressRange::fromArray($entry, $index);
        }

        return $ipRanges;
    }

    public function removeFile(): void
    {
        if ($this->uploadedFile && isset($this->uploadedFile['path'])) {
            Storage::disk('local')->delete($this->uploadedFile['path']);
        }
        $this->reset(['uploadedFile', 'file']);
        session()->flash('info', 'File removed successfully.');
    }

    public function formatBytes($size): string
    {
        if ($size >= 1048576) {
            return number_format($size / 1048576, 2).' MB';
        }
        if ($size >= 1024) {
            return number_format($size / 1024, 2).' KB';
        }

        return $size.' B';
    }

    #[Layout('components.layouts.guest')]
    #[Title('IP Address Import')]
    public function render(): View
    {
        return view('livewire.ip-address-import');
    }
}
