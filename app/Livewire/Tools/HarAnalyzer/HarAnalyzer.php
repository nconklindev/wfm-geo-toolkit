<?php

namespace App\Livewire\Tools\HarAnalyzer;

use App\Services\HarAnalysisService;
use Exception;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Log;
use Storage;

class HarAnalyzer extends Component
{
    use WithFileUploads;

    #[Validate('required|file|max:20480')] // 20MB max
    public $harFile;

    public $uploadedFile = null;

    public function uploadFile(): void
    {
        Log::info('Upload started', ['file_size' => $this->harFile?->getSize()]);

        $this->validate();

        // Custom validation for HAR files
        $extension = strtolower($this->harFile->getClientOriginalExtension());
        if (! in_array($extension, ['har', 'json'])) {
            $this->addError('harFile', 'The file must be a .har or .json file.');

            return;
        }

        try {
            Log::info('Starting file storage');

            $path = $this->harFile->store('har-files', 'local');

            Log::info('File stored successfully', ['path' => $path]);

            $this->uploadedFile = [
                'name' => $this->harFile->getClientOriginalName(),
                'size' => $this->harFile->getSize(),
                'path' => $path,
                'uploaded_at' => now(),
            ];

            $this->harFile = null;
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
            $analyzer = new HarAnalysisService($filePath);

            $analysisData = [
                'overview' => $analyzer->getOverview(),
                'performance' => $analyzer->getPerformanceMetrics(),
                'requests_by_type' => $analyzer->getRequestsByType(),
                'status_codes' => $analyzer->getStatusCodes(),
                'largest_resources' => $analyzer->getLargestResources(),
                'failed_requests' => $analyzer->getFailedRequests(),
                'security' => $analyzer->getSecurityAnalysis(),
                'domains' => $analyzer->getDomainAnalysis(),
                'timeline' => $analyzer->getTimelineData(),
                'recommendations' => $analyzer->getPerformanceRecommendations(),
            ];

            // Use session()->put() instead of session()->flash() for persistent data
            session()->put('analysisData', $analysisData);
            session()->put('uploadedFile', $this->uploadedFile);

            $this->redirect(HarAnalyzerResults::class, navigate: true);
        } catch (Exception $e) {
            session()->flash('error', 'Failed to analyze file: '.$e->getMessage());
            $this->removeFile();
        }
    }

    public function removeFile(): void
    {
        if ($this->uploadedFile && isset($this->uploadedFile['path'])) {
            Storage::disk('local')->delete($this->uploadedFile['path']);
        }
        $this->reset(['uploadedFile', 'harFile']);
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
    #[Title('HAR Analyzer')]
    public function render()
    {
        return view('livewire.tools.har-analyzer.har-analyzer');
    }
}
