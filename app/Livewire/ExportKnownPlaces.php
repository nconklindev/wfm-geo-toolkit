<?php

namespace App\Livewire;

use App\Http\Resources\KnownPlaceResource;
use App\Services\KnownPlaceService;
use Illuminate\Http\Request;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportKnownPlaces extends Component
{

    #[Validate(['in:csv,json'])]
    public string $fileFormat = 'json';

    #[Validate(['in:all,recent,custom'])]
    public string $placesFilter = 'all';

    #[Validate(['array', 'sometimes', 'required_if:placesFilter, custom'])]
    public array $selectedPlaces = [];

    #[Validate(['boolean'])]
    public bool $transformData = false;

    #[Validate(['boolean'])]
    public bool $includeTimestamps = false;

    protected KnownPlaceService $knownPlaceService;

    public function boot(KnownPlaceService $knownPlaceService): void
    {
        $this->knownPlaceService = $knownPlaceService;
    }

    public function mount(): void
    {
        $this->transformData = false;
        $this->includeTimestamps = false;
    }

    public function export(Request $request): ?StreamedResponse
    {
        // Get the authenticated user
        $user = auth()->user();

        // Validate the inputs
        $this->validate();

        // Merge the includeTimestamps property into the include_timestamps request header
        // which will be automatically passed into the KnownPlaceResource where we're conditionally including timestamps or not
        $request->merge(['include_timestamps' => $this->includeTimestamps]);

        // Get the user's Known Places
        $query = $user->knownPlaces();

        // Apply filters based on request parameters
        if ($this->placesFilter === 'recent') {
            $query->where('created_at', '>=', now()->subDays(30)); // Created within last 30 days filter
        } elseif ($this->placesFilter === 'custom') {
            // If places_filter is custom but selected_places is empty, return flash error to page and redirect
            if (empty($this->selectedPlaces)) {
                flash()
                    ->option('position', 'bottom-right')
                    ->option('timeout', 5000)
                    ->error('Please select at least one known place.');
                $this->fill(['placesFilter' => 'custom']);
                return null; // Stop execution on the page, but don't redirect
            }
            $query->whereIn('id', $this->selectedPlaces);
        }

        // Get the filtered known places from the query
        $knownPlaces = $query->get();

        // Check if we have any places to export
        if ($knownPlaces->isEmpty()) {
            flash()
                ->option('position', 'bottom-right')
                ->option('timeout', 5000)
                ->error('No known places found to export.');
            $this->redirect(ExportKnownPlaces::class);
            return null;
        }

        // Create the filename based on the current date
        $fileName = 'known_places_'.now()->format('Y-m-d');

        // Use KnownPlaceResource to format the data
        $resourceCollection = KnownPlaceResource::collection($knownPlaces);

        // Export based on requested format
        $response = match ($this->fileFormat) {
            'csv' => $this->exportAsCsv($resourceCollection->toArray($request), $fileName, $this->transformData),
            default => $this->exportAsJson($resourceCollection, $fileName),
        };

        // Reset the form
        $this->reset();
        return $response;


    }

    /**
     * Export data as CSV using a Streamed Response
     *
     * @param  array  $data
     * @param  string  $fileName
     * @param  bool  $doTransformData
     * @return StreamedResponse
     */
    private function exportAsCsv(array $data, string $fileName, bool $doTransformData): StreamedResponse
    {
        // Sanitize filename
        $safeFileName = preg_replace('/[\x00-\x1F\x7F\/:*?"<>|]/', '_', $fileName);
        $safeFileName = substr($safeFileName, 0, 200);
        $safeFileName = trim($safeFileName, ' .');
        $fullFileName = $safeFileName.'.csv';

        if ($doTransformData) {
            $data = $this->knownPlaceService->transformDataForExport($data, true);
        }

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$fullFileName.'"',
        ];

        return response()->streamDownload(function () use ($data, $doTransformData) {
            $output = fopen('php://output', 'w'); // Write directly to PHP's output buffer

            if (!empty($data)) {
                // Add headers based on whether the data is transformed or not
                if ($doTransformData) {
                    // For transformed data, use specific headers for the Pro WFM Data Import Tool
                    fputcsv($output, ['name', 'latitude', 'longitude', 'radius', 'locations', 'validation_order']);
                } else {
                    // For regular data, use the first item's keys
                    if (isset($data[0]) && is_array($data[0])) {
                        fputcsv($output, array_keys($data[0]));
                    }
                }

                // Add rows
                foreach ($data as $row) {
                    // Sanitize for CSV Injection (from point 1)
                    foreach ($row as $key => $value) {
                        if (is_array($value)) {
                            $row[$key] = json_encode($value);
                        } elseif (is_string($value) && strlen($value) > 0) {
                            $firstChar = $value[0];
                            // This is checking if the characters are in a "naughty list" and replacing them with a single quote
                            // We can't really check for minus symbols because that messes with the coordinates output since some will be negative
                            if (in_array($firstChar, ['=', '+', '@'], true)) {
                                $row[$key] = "'".$value;
                            }
                        }
                    }
                    fputcsv($output, $row);
                }
            }
            fclose($output); // Close the output stream
        }, $fullFileName, $headers);
    }

    /**
     * Export data as JSON using a Streamed JSON Response
     *
     * @param  mixed  $data
     * @param  string  $fileName
     * @return StreamedResponse
     */
    private function exportAsJson(mixed $data, string $fileName): StreamedResponse
    {
        $headers = [
            'Content-Disposition' => 'attachment; filename="'.$fileName.'.json"',
            'Content-Type' => 'application/json',
        ];

        return response()->streamDownload(function () use ($data) {
            echo json_encode($data, JSON_PRETTY_PRINT);
        }, $fileName, $headers);
    }

    #[Layout('components.layouts.app')]
    #[Title('Export Known Places')]
    public function render()
    {
        $user = auth()->user();
        return view('livewire.export-known-places', compact('user'));
    }
}
