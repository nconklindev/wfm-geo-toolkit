<?php

namespace App\Services;

use App\Models\BusinessStructureNode;
use App\Models\KnownPlace;
use App\Models\User;
use Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use JsonException;

class KnownPlaceService
{
    /**
     * Process an uploaded JSON file and create KnownPlace records
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

                        // Attach business structure nodes for updated place
                        $this->attachBusinessStructureNodes($duplicate, $user);

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
     */
    public function mapJsonDataToKnownPlace(array $sourceData): array
    {
        return [
            'name' => $sourceData['name'],
            'latitude' => $sourceData['latitude'],
            'longitude' => $sourceData['longitude'],
            'radius' => $sourceData['radius'],
            'accuracy' => $sourceData['accuracy'],
            'validation_order' => isset($sourceData['validationOrder']) && is_array($sourceData['validationOrder']) ?
                array_map('strtolower', $sourceData['validationOrder']) : [],
            'is_active' => $sourceData['active'] ?? true,
            'locations' => $sourceData['locations'] ?? [], // Make sure locations are included
        ];
    }

    /**
     * Create and attach business structure nodes for a known place based on its locations
     */
    public function attachBusinessStructureNodes(KnownPlace $knownPlace, User $user): void
    {
        $locations = $knownPlace->locations ?? [];

        if (empty($locations)) {
            Log::info("KnownPlaceService: No locations found for KnownPlace {$knownPlace->id}, skipping node attachment.");
            return;
        }

        Log::info("KnownPlaceService: Processing locations for KnownPlace {$knownPlace->id}: ".implode(', ',
                $locations));

        $nodeIdsToAttach = [];

        // Process each location path
        foreach ($locations as $locationPath) {
            // Split the location path into segments
            $locationSegments = explode('/', $locationPath);
            $locationSegments = array_map('trim', $locationSegments);
            $locationSegments = array_filter($locationSegments, fn($segment) => $segment !== '');

            if (empty($locationSegments)) {
                continue;
            }

            // Find or create the leaf node for this path
            $leafNode = $this->findOrCreateNodeByPathSegments($locationSegments, $user);
            if ($leafNode) {
                $nodeIdsToAttach[$leafNode->id] = [
                    'user_id' => $user->id,
                    'path' => $leafNode->path,
                    'path_hierarchy' => $locationSegments,
                ];

                Log::info("KnownPlaceService: Prepared to attach node {$leafNode->id} (path: {$leafNode->path}) to KnownPlace {$knownPlace->id}");
            }
        }

        // Attach nodes with pivot data if we have any
        if (!empty($nodeIdsToAttach)) {
            $knownPlace->nodes()->syncWithoutDetaching($nodeIdsToAttach);
            Log::info("KnownPlaceService: Attached ".count($nodeIdsToAttach)." nodes to KnownPlace {$knownPlace->id}");
        }
    }

    /**
     * Import a known place from API data and assign to a user
     */
    public function importFromApiData(array $apiData, User $user): KnownPlace
    {
        $modelData = $this->mapJsonDataToKnownPlace($apiData);

        $knownPlace = $user->knownPlaces()->create($modelData);

        // Attach business structure nodes based on locations
        $this->attachBusinessStructureNodes($knownPlace, $user);

        return $knownPlace;
    }

    /**
     * Finds or creates the necessary BusinessStructureNode hierarchy for a given path.
     * Returns the final leaf node.
     *
     * This mirrors the logic from KnownPlaceController::findOrCreateNodeByPathSegments
     */
    private function findOrCreateNodeByPathSegments(array $locationSegments, User $user): ?BusinessStructureNode
    {
        $parentId = null;
        $currentPath = '';
        $lastNode = null;

        foreach ($locationSegments as $segmentName) {
            // Prepare the node name
            $nodeName = trim(ucfirst($segmentName));

            // Skip empty segments
            if (empty($nodeName)) {
                continue;
            }

            // Build the path string incrementally
            $currentPath = $currentPath ? $currentPath.'/'.$nodeName : $nodeName;

            // Find existing or create a new node for the current segment
            // Uniqueness is based on user, name, and parent_id
            $node = $user->nodes()->updateOrCreate(
                [
                    'name' => $nodeName,
                    'parent_id' => $parentId,
                ],
                [
                    'path' => $currentPath, // Ensure path is set on create/update
                ]
            );

            // Ensure the path is correctly set even if the node already existed
            // This handles cases where the path might not have been stored previously
            if ($node->path !== $currentPath) {
                $node->path = $currentPath;
                $node->save();
            }

            $parentId = $node->id;
            $lastNode = $node; // Track the most recently processed node
        }

        // Return the last node processed (the leaf node for this path)
        return $lastNode;
    }

    /**
     * Transform known place data for export to external tools
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
