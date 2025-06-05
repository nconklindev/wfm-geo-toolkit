<?php

namespace App\Livewire;

use App\Services\KnownIpAddressService;
use Exception;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

class ImportKnownIpAddresses extends Component
{
    use WithFileUploads;

    public $file;
    public string $duplicateHandling = 'skip';
    public string $matchBy = 'name';
    public bool $validateIpRanges = true;
    public ?array $previewData = null;
    public int $importedCount = 0;
    public int $skippedCount = 0;
    public int $updatedCount = 0;
    public array $errors = [];

    protected $rules = [
        'file' => 'required|file|mimes:json|max:10240', // 10MB max
        'duplicateHandling' => 'required|in:skip,replace,update',
        'matchBy' => 'required|in:name,ip_range,both',
        'validateIpRanges' => 'boolean',
    ];

    public function import(KnownIpAddressService $knownIpAddressService)
    {
        $this->validate();

        if (!$this->file || !$this->previewData) {
            $this->addError('file', 'Please upload a valid JSON file first.');
            return;
        }

        $this->resetCounters();

        try {
            // Transform data from the new format to service format if needed
            $serviceData = $this->transformDataForService($this->previewData);

            $results = $knownIpAddressService->importFromArray(
                $serviceData,
                auth()->user(),
                $this->duplicateHandling,
                $this->matchBy,
                $this->validateIpRanges
            );

            $this->importedCount = $results['imported'];
            $this->updatedCount = $results['updated'];
            $this->skippedCount = $results['skipped'];
            $this->errors = $results['errors'];

            $this->showSuccessMessage();

        } catch (Exception $e) {
            $this->addError('import', 'Import failed: '.$e->getMessage());
            Log::error('IP Address import failed: '.$e->getMessage());
        }
    }

    protected function resetCounters()
    {
        $this->importedCount = 0;
        $this->skippedCount = 0;
        $this->updatedCount = 0;
        $this->errors = [];
    }

    /**
     * Transform data from new JSON format to service format
     */
    protected function transformDataForService(array $data): array
    {
        return array_map(function ($entry) {
            return [
                'name' => $entry['name'] ?? '',
                'description' => $entry['description'] ?? null,
                'start' => $entry['startingIPRange'] ?? $entry['start'] ?? '',
                'end' => $entry['endingIPRange'] ?? $entry['end'] ?? '',
            ];
        }, $data);
    }

    protected function showSuccessMessage()
    {
        $message = "Import completed! ";
        $details = [];

        if ($this->importedCount > 0) {
            $details[] = "{$this->importedCount} imported";
        }
        if ($this->updatedCount > 0) {
            $details[] = "{$this->updatedCount} updated";
        }
        if ($this->skippedCount > 0) {
            $details[] = "{$this->skippedCount} skipped";
        }

        $message .= implode(', ', $details);

        if (!empty($this->errors)) {
            $message .= ". ".count($this->errors)." errors occurred.";
        }

        session()->flash('message', $message);

        if (!empty($this->errors)) {
            session()->flash('errors', $this->errors);
        }

        // Reset form
        $this->reset(['file', 'previewData']);
    }

    #[Layout('components.layouts.app')]
    #[Title('Import Known IP Addresses')]
    public function render()
    {
        return view('livewire.import-known-ip-addresses')
            ->layout('layouts.app', ['title' => 'Import Known IP Addresses']);
    }

    public function updatedFile()
    {
        $this->validateOnly('file');
        $this->generatePreview();
    }

    public function generatePreview()
    {
        if (!$this->file) {
            $this->previewData = null;
            return;
        }

        try {
            $content = file_get_contents($this->file->getRealPath());
            $data = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->addError('file', 'Invalid JSON format: '.json_last_error_msg());
                return;
            }

            if (!is_array($data)) {
                $this->addError('file', 'JSON must contain an array of IP address entries.');
                return;
            }

            $this->previewData = $data;
            $this->resetErrorBag('file');

        } catch (Exception $e) {
            $this->addError('file', 'Error reading file: '.$e->getMessage());
            Log::error('Error generating preview for IP import: '.$e->getMessage());
        }
    }
}
