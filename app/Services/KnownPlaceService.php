<?php

namespace App\Services;

use App\Models\KnownPlace;
use App\Models\User;
use Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use JsonException;

class KnownPlaceService
{
    /**
     * Process an uploaded JSON file and create KnownPlace records
     *
     * @param  UploadedFile  $file  The uploaded JSON file
     * @param  User  $user  The user who owns these known places
     * @param  array  $options  Additional import options
     *
     * @return array{success: bool, count: int, message: string}
     */
    public function processUploadedFile(UploadedFile $file, User $user, array $options = []): array
    {
        try {
            // Get the contents and decode the JSON file
            $contents = $file->get();
            $data = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);

            // Process the data based on options
            $duplicateHandling = $options['duplicate_handling'] ?? 'skip';
            $matchBy = $options['match_by'] ?? 'name';
            $includeInactive = $options['include_inactive'] ?? false;

            // Filter data if needed
            if (!$includeInactive) {
                $data = array_filter($data, fn($item) => $item['active'] ?? true);
            }

            // Import the data
            $createdPlaces = $this->batchImportFromApiData($data, $user, [
                'duplicate_handling' => $duplicateHandling,
                'match_by' => $matchBy,
            ]);

            return [
                'success' => true,
                'count' => count($createdPlaces),
                'message' => "Successfully imported ".count($createdPlaces)." known places."
            ];

        } catch (FileNotFoundException $e) {
            Log::error('Error reading file: '.$e->getMessage());
            return [
                'success' => false,
                'count' => 0,
                'message' => 'Unable to read the uploaded file.'
            ];
        } catch (JsonException $e) {
            Log::error('JSON parsing error: '.$e->getMessage());
            return [
                'success' => false,
                'count' => 0,
                'message' => 'Invalid JSON format in the uploaded file.'
            ];
        } catch (Exception $e) {
            Log::error('Unexpected error processing file: '.$e->getMessage());
            return [
                'success' => false,
                'count' => 0,
                'message' => 'An error occurred while processing the file.'
            ];
        }
    }

    /**
     * Batch import multiple known places from API data
     *
     * @param  array  $apiDataCollection  Collection of API data items
     * @param  User  $user  The user who owns these known places
     * @param  array  $options  Import options (duplicate_handling, match_by)
     *
     * @return array The created known places
     */
    public function batchImportFromApiData(array $apiDataCollection, User $user, array $options = []): array
    {
        $createdPlaces = [];
        $duplicateHandling = $options['duplicate_handling'] ?? 'skip';
        $matchBy = $options['match_by'] ?? 'name';

        foreach ($apiDataCollection as $apiData) {
            // Check for duplicates based on options
            $duplicate = $this->findDuplicate($apiData, $user, $matchBy);

            if ($duplicate) {
                switch ($duplicateHandling) {
                    case 'skip':
                        // Skip this item
                        continue 2;

                    case 'update':
                        // Update the existing record
                        $modelData = $this->mapJsonDataToKnownPlace($apiData);
                        $duplicate->update($modelData);
                        $createdPlaces[] = $duplicate;
                        continue 2;

                    case 'replace':
                        // Delete and recreate
                        $duplicate->delete();
                        break;
                }
            }

            // Create new record
            $createdPlaces[] = $this->importFromApiData($apiData, $user);
        }

        return $createdPlaces;
    }

    /**
     * Find duplicate known place based on match criteria
     *
     * Used in exporting data out of Geo Toolkit
     *
     * @param  array  $apiData  API data to check against
     * @param  User  $user  The user who owns the known places
     * @param  string  $matchBy  How to match duplicates: 'name', 'coordinates', or 'both'
     *
     * @return KnownPlace|null  The duplicate if found, null otherwise
     */
    protected function findDuplicate(array $apiData, User $user, string $matchBy): ?KnownPlace
    {
        $query = $user->knownPlaces();

        return match ($matchBy) {
            'name' => $query->where('name', $apiData['name'])->first(),
            'coordinates' => $query
                ->where('latitude', $apiData['latitude'])
                ->where('longitude', $apiData['longitude'])
                ->first(),
            'both' => $query
                ->where('name', $apiData['name'])
                ->where('latitude', $apiData['latitude'])
                ->where('longitude', $apiData['longitude'])
                ->first(),
            default => null,
        };
    }

    /**
     * Map API data to the KnownPlace model structure
     *
     * Used in importing Known Places into Geo Toolkit
     *
     * @param  array  $sourceData  Data from external API
     *
     * @return array Mapped data for KnownPlace model
     */
    public function mapJsonDataToKnownPlace(array $sourceData): array
    {
        return [
            'name' => $sourceData['name'],
            'latitude' => $sourceData['latitude'],
            'longitude' => $sourceData['longitude'],
            'radius' => $sourceData['radius'],
            'accuracy' => $sourceData['accuracy'],
            'validation_order' => isset($sourceData['validationOrder']) && is_array($sourceData['validationOrder']) ? array_map('strtolower',
                $sourceData['validationOrder']) : [],
            'is_active' => $sourceData['active'] ?? true,
        ];
    }

    /**
     * Import a known place from API data and assign to a user
     *
     * @param  array  $apiData  Data from external API
     * @param  User  $user  The user who owns this known place
     *
     * @return KnownPlace The created known place
     */
    public function importFromApiData(array $apiData, User $user): KnownPlace
    {
        $modelData = $this->mapJsonDataToKnownPlace($apiData);

        return $user->knownPlaces()->create($modelData);
    }

    /**
     * Transform known place data for export to external tools
     *
     * @param  array|Collection  $knownPlaces  Collection of KnownPlace objects or array of data
     * @param  bool  $doTransformData  Whether to transform data for external tool format
     *
     * @return array  Transformed data ready for export
     * @uses KnownPlace
     */
    public function transformDataForExport(Collection|array $knownPlaces, bool $doTransformData): array
    {
        // Convert Collection to array if needed
        $dataArray = $knownPlaces instanceof Collection ? $knownPlaces->toArray() : $knownPlaces;
        $result = [];


        foreach ($dataArray as $place) {
            if ($doTransformData) {
                // Transform data for external tool format
                $locations = $place['locations'] ?? [];
                $validationOrder = $place['validation_order'] ?? [];

                // Ensure validation_order is an array we can implode
                if (is_string($validationOrder)) {
                    $validationOrder = json_decode($validationOrder, true) ?? [];
                }

                // Make sure it's actually an array before imploding
                if (!is_array($validationOrder)) {
                    $validationOrder = [];
                }

                // Create transformed item with only needed fields
                $transformedItem = [
                    'name' => $place['name'] ?? '',
                    'latitude' => $place['latitude'] ?? 0,
                    'longitude' => $place['longitude'] ?? 0,
                    'radius' => $place['radius'] ?? 0,
                    'locations' => implode(',', $locations),
                    'validation_order' => implode(',', $validationOrder),
                ];

                $result[] = $transformedItem;
            } else {
                // For normal export, exclude timestamp columns
                $filteredPlace = array_filter($place, function ($key) {
                    return !in_array($key, ['created_at', 'updated_at', 'deleted_at']);
                }, ARRAY_FILTER_USE_KEY);

                $result[] = $filteredPlace;
            }

        }
        return $result;
    }

}
