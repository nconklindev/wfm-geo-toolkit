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

        // Check if file upload failed due to PHP limits
        if (!$this->harFile || $this->harFile->getError() !== UPLOAD_ERR_OK) {
            $uploadError = $this->harFile ? $this->harFile->getError() : UPLOAD_ERR_NO_FILE;
            $errorMessage = $this->getUploadErrorMessage($uploadError);
            $this->addError('harFile', $errorMessage);
            return;
        }

        // Check file size against effective limits before validation
        $limits = $this->getUploadLimits();
        if ($this->harFile->getSize() > $limits['effective_limit']) {
            $fileSizeMB = round($this->harFile->getSize() / (1024 * 1024), 1);
            $maxSizeMB = $limits['effective_limit_mb'];
            $this->addError('harFile', "File is too large ({$fileSizeMB}MB). Maximum allowed size is {$maxSizeMB}MB. Please choose a smaller file or contact your administrator to increase upload limits.");
            return;
        }

        $this->validate();

        // Custom validation for HAR files
        $extension = strtolower($this->harFile->getClientOriginalExtension());
        if (! in_array($extension, ['har', 'json'])) {
            $this->addError('harFile', 'The file must be a .har or .json file.');
            return;
        }

        // Skip detailed validation for now to avoid JSON parsing issues
        // The HarAnalysisService will handle validation during analysis

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

    private function getUploadErrorMessage(int $errorCode): string
    {
        $phpUploadMax = ini_get('upload_max_filesize');
        $phpPostMax = ini_get('post_max_size');

        return match($errorCode) {
            UPLOAD_ERR_INI_SIZE => "File is too large. Maximum allowed size is {$phpUploadMax}. Please reduce your file size or contact your administrator.",
            UPLOAD_ERR_FORM_SIZE => "File exceeds the maximum allowed size for this form.",
            UPLOAD_ERR_PARTIAL => "File upload was interrupted. Please try again.",
            UPLOAD_ERR_NO_FILE => "No file was selected for upload.",
            UPLOAD_ERR_NO_TMP_DIR => "Server error: Missing temporary folder. Please contact your administrator.",
            UPLOAD_ERR_CANT_WRITE => "Server error: Cannot write file to disk. Please contact your administrator.",
            UPLOAD_ERR_EXTENSION => "File upload was blocked by a server extension.",
            default => "Upload failed with unknown error code: {$errorCode}. Please try again."
        };
    }

    public function getUploadLimits(): array
    {
        $uploadMax = $this->parseSize(ini_get('upload_max_filesize'));
        $postMax = $this->parseSize(ini_get('post_max_size'));
        $livewireMax = 20480 * 1024; // 20MB from validation rule
        
        // The effective limit is the smallest of the three
        $effectiveLimit = min($uploadMax, $postMax, $livewireMax);
        
        return [
            'upload_max_filesize' => $uploadMax,
            'post_max_size' => $postMax,
            'livewire_max' => $livewireMax,
            'effective_limit' => $effectiveLimit,
            'effective_limit_mb' => round($effectiveLimit / (1024 * 1024), 1)
        ];
    }

    private function parseSize(string $size): int
    {
        $size = trim($size);
        $last = strtolower($size[strlen($size)-1]);
        $value = (int) $size;
        
        switch($last) {
            case 'g': $value *= 1024;
            case 'm': $value *= 1024;
            case 'k': $value *= 1024;
        }
        
        return $value;
    }

    #[Layout('components.layouts.guest')]
    #[Title('HAR Analyzer')]
    public function render()
    {
        return view('livewire.tools.har-analyzer.har-analyzer', [
            'uploadLimits' => $this->getUploadLimits()
        ]);
    }
}
