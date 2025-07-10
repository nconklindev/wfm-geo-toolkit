<?php

namespace App\Livewire\Tools;

use App\Point;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Plotter extends Component
{
    public const MAX_ACCURACY = 1000;

    // Explicitly set the between values as floats to prevent the validation from failing
    #[Validate('required|decimal:0,10|between:-90.00,90.00')]
    public float $latitude;

    #[Validate('required|decimal:0,10|between:-180.00,180.00')]
    public float $longitude;

    #[Validate('required|integer|between:1,1000')]
    public int $radius;

    #[Validate('required|integer|between:1,9999')]
    public int $accuracy;

    #[Validate('nullable|string|max:255')]
    public ?string $label;

    #[Validate('nullable|hex_color')]
    public string $color = '#3b82f6';

    #[Validate('required|string|in:known_place,punch')]
    public string $type = 'known_place';

    #[Validate([
        'locations' => 'array',
        'locations.*' => [
            'string',
            'max:255',
        ],
    ])]
    public array $locations = [];

    /**
     * @var Point[]
     */
    public array $points = [];

    /**
     * @var array<int, array> Array of punch validation issues indexed by point index
     */
    public array $punchIssues = [];

    public $selectedPointForIssues = null;

    public $selectedSeverity = null;

    public function selectPointForIssues($index, $severity = null): void
    {
        $this->selectedPointForIssues = $index;
        $this->selectedSeverity = $severity;
    }

    /**
     * Add a point and dispatch an event for the map to listen for
     */
    public function addPoint(): void
    {
        $this->validate();

        $point = new Point(
            latitude: $this->latitude,
            longitude: $this->longitude,
            label: $this->label ?? '',
            type: $this->type,
            locations: $this->locations,
            radius: $this->radius,
            accuracy: $this->accuracy,
            color: $this->color
        );

        $this->points[] = $point;

        // Dispatch event with the new point to update the map
        $this->dispatch('points-updated', $this->formatPointsForMap());

        $this->checkPunch($point);

        $this->reset('latitude', 'longitude', 'label', 'radius', 'accuracy', 'color');
        $this->locations = [];

        $this->dispatch('reset-locations')->to(PlotterLocationInput::class);
    }

    /**
     * Format points for the map
     */
    private function formatPointsForMap(): array
    {
        // Ensure this formats the points correctly for the JS update function
        return array_map(function ($index, $point) {
            // Ensure Point properties are accessed correctly
            if (! $point instanceof Point) {
                return null;
            } // Basic safety check

            return [
                'id' => $index,
                'latitude' => $point->latitude,
                'longitude' => $point->longitude,
                'label' => $point->label,
                'radius' => $point->radius,
                'accuracy' => $point->accuracy, // Assuming Point has accuracy
                'color' => $point->color, // Assuming Point has color
            ];
        }, array_keys($this->points), $this->points);

    }

    /**
     * Perform validation checks on the given punch point and store issues if found.
     *
     * - Verifies if the point is of type 'punch' before proceeding.
     * - Checks the punch's boundary against known places.
     * - Assesses accuracy limits for the punch against global and place-specific constraints.
     * - Logs validation results and issues.
     * - Dispatches an event with punch validation data.
     *
     * @param  Point  $punch  The punch point to validate.
     */
    private function checkPunch(Point $punch): void
    {
        // Only proceed if this point is actually a punch type
        if ($punch->type !== 'punch') {
            return;
        }

        // Get the index of this punch in the points array
        $punchIndex = array_search($punch, $this->points, true);

        // Initialize issues array for this punch
        $issues = [];

        // Get all known places to determine if boundary checking is applicable
        $knownPlaces = array_filter($this->points, function ($point) {
            return $point->type === 'known_place';
        });

        // Get the closest known place data once for all checks that need it
        $closestData = null;
        $closestKnownPlace = null;

        if (! empty($knownPlaces)) {
            $closestData = $this->getClosestKnownPlace($punch);
            $closestKnownPlace = $closestData['known_place'] ?? null;
        }

        // Only check boundary if there are known places to check against
        if (! empty($knownPlaces)) {
            $isInBoundary = $this->isPunchInKnownPlaceBoundary($punch);

            if (! $isInBoundary) {
                $issues[] = [
                    'type' => 'outside_boundary',
                    'severity' => 'critical',
                    'message' => 'Punch is outside any known place boundary',
                ];
            }
        }
        // If no known places exist, we don't flag this as an issue

        // Check if punch accuracy exceeds the maximum allowed
        if ($this->isPunchOverMaxAccuracy($punch)) {
            $issues[] = [
                'type' => 'accuracy_exceeded_max',
                'severity' => 'info',
                'message' => "Punch accuracy ({$punch->accuracy}m) exceeds maximum allowed (".self::MAX_ACCURACY.'m)',
            ];
        }

        // Check if punch accuracy exceeds the closest Known Place accuracy
        if ($this->isPunchOverKnownPlaceAccuracy($punch) && $closestKnownPlace) {
            $issues[] = [
                'type' => 'low_accuracy',
                'severity' => 'info',
                'message' => "Punch accuracy ({$punch->accuracy}m) is lower than closest known place accuracy ({$closestKnownPlace->accuracy}m)",
            ];
        }

        // Store issues for this punch
        if ($punchIndex !== false) {
            $this->punchIssues[$punchIndex] = $issues;
        }

        // Log results
        if (empty($issues)) {
            Log::info('Punch validation passed', [
                'punch_coordinates' => [$punch->latitude, $punch->longitude],
                'punch_label' => $punch->label,
                'punch_accuracy' => $punch->accuracy,
                'known_places_count' => count($knownPlaces),
                'closest_known_place' => $closestKnownPlace ? $closestKnownPlace->label : null,
            ]);
        } else {
            Log::warning('Punch validation issues detected', [
                'punch_coordinates' => [$punch->latitude, $punch->longitude],
                'punch_label' => $punch->label,
                'punch_accuracy' => $punch->accuracy,
                'known_places_count' => count($knownPlaces),
                'closest_known_place' => $closestKnownPlace ? $closestKnownPlace->label : null,
                'issues' => $issues,
            ]);
        }

        // Dispatch event with validation results
        $this->dispatch('punch-validated', [
            'punch_index' => $punchIndex,
            'punch' => [
                'latitude' => $punch->latitude,
                'longitude' => $punch->longitude,
                'label' => $punch->label,
                'accuracy' => $punch->accuracy,
            ],
            'closest_known_place' => $closestKnownPlace ? [
                'label' => $closestKnownPlace->label,
                'distance' => $closestData['distance'],
            ] : null,
            'issues' => $issues,
            'has_issues' => ! empty($issues),
        ]);
    }

    /**
     * Perform validation checks on the given punch point and store issues if found.
     *
     * - Verifies if the point is of type 'punch' before proceeding.
     * - Checks the punch's boundary against ALL applicable known places.
     * - Assesses accuracy limits for the punch against global and place-specific constraints.
     * - Logs validation results and issues.
     * - Dispatches an event with punch validation data.
     *
     * @param  Point  $punch  The punch point to validate.
     */

    /**
     * Find the closest known place to a given punch
     * Returns array with the closest known place and distance, or null if no known places exist
     */
    private function getClosestKnownPlace(Point $punch): ?array
    {
        $knownPlaces = array_filter($this->points, function ($point) {
            return $point->type === 'known_place';
        });

        if (empty($knownPlaces)) {
            return null;
        }

        $closestKnownPlace = null;
        $shortestDistance = PHP_FLOAT_MAX;

        foreach ($knownPlaces as $knownPlace) {
            $distance = $this->calculateDistance(
                $punch->latitude,
                $punch->longitude,
                $knownPlace->latitude,
                $knownPlace->longitude
            );

            if ($distance < $shortestDistance) {
                $shortestDistance = $distance;
                $closestKnownPlace = $knownPlace;
            }
        }

        return [
            'known_place' => $closestKnownPlace,
            'distance' => $shortestDistance,
        ];
    }

    /**
     * Calculate distance between two coordinates using Haversine formula
     * Returns distance in meters
     */
    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // Earth's radius in meters

        // Convert to radians
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    private function isPunchInKnownPlaceBoundary(Point $punch): bool
    {
        // Only check punch type points
        if ($punch->type !== 'punch') {
            return false;
        }

        // Find the closest known place
        $closestData = $this->getClosestKnownPlace($punch);

        // If no known places exist, punch cannot be tied to one
        if ($closestData === null) {
            return false;
        }

        $closestKnownPlace = $closestData['known_place'];
        $shortestDistance = $closestData['distance'];

        // Check if punch is within the closest known place's radius
        $isWithinBoundary = $shortestDistance <= $closestKnownPlace->radius;

        Log::info('Punch boundary check', [
            'punch_label' => $punch->label,
            'punch_coordinates' => [$punch->latitude, $punch->longitude],
            'closest_known_place_label' => $closestKnownPlace->label,
            'closest_known_place_coordinates' => [$closestKnownPlace->latitude, $closestKnownPlace->longitude],
            'distance_to_closest' => round($shortestDistance, 2),
            'known_place_radius' => $closestKnownPlace->radius,
            'is_within_boundary' => $isWithinBoundary,
        ]);

        return $isWithinBoundary;
    }

    /**
     * Calculate if the punch accuracy is over the max accuracy value
     */
    private function isPunchOverMaxAccuracy(Point $punch): bool
    {
        // Only check punch type points
        if ($punch->type !== 'punch') {
            return false;
        }

        if ($punch->accuracy > self::MAX_ACCURACY) {
            return true;
        } else {
            return false;
        }
    }

    private function isPunchOverKnownPlaceAccuracy(Point $punch): bool
    {
        if ($punch->type !== 'punch') {
            return false;
        }

        // Get all known places to check against
        $knownPlaces = array_filter($this->points, function ($point) {
            return $point->type === 'known_place';
        });

        if (empty($knownPlaces)) {
            return false;
        }

        // Find the closest known place
        $closestData = $this->getClosestKnownPlace($punch);

        // If no known places exist, punch cannot be tied to one
        if ($closestData === null) {
            return false;
        }

        $closestKnownPlace = $closestData['known_place'];

        // Check if punch accuracy is worse (higher) than the closest known place accuracy
        return $punch->accuracy > $closestKnownPlace->accuracy;
    }

    #[On('locations-updated')]
    public function updateLocations($locations): void
    {
        $this->locations = $locations;
    }

    public function increaseRadius(): void
    {
        $step = 5;

        if ($this->radius + $step > 1000) {
            $this->radius = 1000;
        }

        $this->radius += $step;
    }

    /**
     * Get issues for a specific punch filtered by severity
     */
    public function getPunchIssuesBySeverity(int $punchIndex, string $severity): array
    {
        $allIssues = $this->getPunchIssues($punchIndex);

        return array_filter($allIssues, function ($issue) use ($severity) {
            return $issue['severity'] === $severity;
        });
    }

    /**
     * Get all issues for a specific punch by index
     */
    public function getPunchIssues(int $punchIndex): array
    {
        return $this->punchIssues[$punchIndex] ?? [];
    }

    /**
     * Get all punches that have issues
     */
    public function getPunchesWithIssues(): array
    {
        $punchesWithIssues = [];

        foreach ($this->punchIssues as $punchIndex => $issues) {
            if (! empty($issues) && isset($this->points[$punchIndex])) {
                $punchesWithIssues[$punchIndex] = [
                    'point' => $this->points[$punchIndex],
                    'issues' => $issues,
                ];
            }
        }

        return $punchesWithIssues;
    }

    /**
     * Check if there are any punch validation issues
     */
    public function hasPunchIssues(): bool
    {
        foreach ($this->punchIssues as $issues) {
            if (! empty($issues)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get count of punches with issues by severity
     */
    public function getIssuesSummary(): array
    {
        $summary = [
            'critical' => 0,
            'warning' => 0,
            'info' => 0,
            'total_punches_with_issues' => 0,
        ];

        foreach ($this->punchIssues as $issues) {
            if (! empty($issues)) {
                $summary['total_punches_with_issues']++;

                foreach ($issues as $issue) {
                    $severity = $issue['severity'] ?? 'info';
                    if (isset($summary[$severity])) {
                        $summary[$severity]++;
                    }
                }
            }
        }

        return $summary;
    }

    /**
     * Get severity configuration for styling
     */
    public function getSeverityConfig(string $severity): array
    {
        return match ($severity) {
            'critical' => [
                'icon' => 'x-circle',
                'text-color' => 'text-red-600 dark:text-red-400',
                'bg-color' => 'bg-red-50 dark:bg-red-900/20',
                'bg-color-inner' => 'bg-red-100 dark:bg-red-900/40',
                'border-color' => 'border-red-200 dark:border-red-800',
                'hover_decoration' => 'hover:decoration-red-600',
                'hover_bg' => 'hover:bg-red-100/60 dark:hover:bg-red-900/30',
            ],
            'warning' => [
                'icon' => 'exclamation-triangle',
                'text-color' => 'text-amber-600 dark:text-amber-400',
                'bg-color' => 'bg-amber-50 dark:bg-amber-900/20',
                'bg-color-inner' => 'bg-amber-100 dark:bg-amber-900/40',
                'border-color' => 'border-amber-200 dark:border-amber-800',
                'hover_decoration' => 'hover:decoration-amber-600',
                'hover_bg' => 'hover:bg-amber-100/60 dark:hover:bg-amber-900/30',
            ],
            'info' => [
                'icon' => 'information-circle',
                'text-color' => 'text-blue-600 dark:text-blue-400',
                'bg-color' => 'bg-blue-50 dark:bg-blue-900/20',
                'bg-color-inner' => 'bg-blue-100 dark:bg-blue-900/40',
                'border-color' => 'border-blue-200 dark:border-blue-800',
                'hover_decoration' => 'hover:decoration-blue-600',
                'hover_bg' => 'hover:bg-blue-100/60 dark:hover:bg-blue-900/30',
            ],
            default => [
                'icon' => 'information-circle',
                'text-color' => 'text-zinc-600 dark:text-zinc-400',
                'bg-color' => 'bg-zinc-50 dark:bg-zinc-900/20',
                'bg-color-inner' => 'bg-zinc-100 dark:bg-zinc-900/40',
                'border-color' => 'border-zinc-200 dark:border-zinc-800',
                'hover_decoration' => 'hover:decoration-zinc-600',
                'hover_bg' => 'hover:bg-zinc-100/60 dark:hover:bg-zinc-900/30',
            ],
        };
    }

    public function decreaseRadius()
    {
        $step = 5;

        if ($this->radius - $step < 0) {
            $this->radius = 0;
        }

        $this->radius -= $step;
    }

    /**
     * Get the highest severity from a collection of issues
     */
    public function getHighestSeverity(array $issues): string
    {
        $severityOrder = ['info' => 1, 'warning' => 2, 'critical' => 3];
        $highestSeverity = 'info';
        $highestLevel = 0;

        foreach ($issues as $issue) {
            $severity = $issue['severity'] ?? 'info';
            $level = $severityOrder[$severity] ?? 1;

            if ($level > $highestLevel) {
                $highestLevel = $level;
                $highestSeverity = $severity;
            }
        }

        return $highestSeverity;
    }

    public function removePoint(int $index): void
    {
        if (isset($this->points[$index])) {
            unset($this->points[$index]);
            unset($this->punchIssues[$index]); // Remove associated issues

            $this->points = array_values($this->points); // Re-index the array

            // Re-index the issues array to match the new points array
            $reindexedIssues = [];
            $newIndex = 0;
            foreach ($this->punchIssues as $oldIndex => $issues) {
                if ($oldIndex < $index) {
                    $reindexedIssues[$newIndex] = $issues;
                    $newIndex++;
                } elseif ($oldIndex > $index) {
                    $reindexedIssues[$newIndex] = $issues;
                    $newIndex++;
                }
                // Skip the removed index
            }
            $this->punchIssues = $reindexedIssues;

            // Clear selection if the removed point was selected
            if ($this->selectedPointForIssues === $index) {
                $this->clearSelectedPoint();
            } elseif ($this->selectedPointForIssues > $index) {
                // Adjust the selected index if it's after the removed point
                $this->selectedPointForIssues--;
            }

            // Inform the map that points have changed - need to refresh all points
            $this->dispatch('points-updated', $this->formatPointsForMap());
        }
    }

    public function clearSelectedPoint(): void
    {
        $this->selectedPointForIssues = null;
        $this->selectedSeverity = null;
    }

    public function clearAllPoints(): void
    {
        $this->points = [];
        $this->punchIssues = []; // Clear all issues when clearing points
        $this->clearSelectedPoint(); // Clear any selection
        $this->dispatch('points-updated', []);
    }

    public function flyTo(int $index): void
    {
        if (isset($this->points[$index])) {
            $point = $this->points[$index];
            // Dispatch a browser event with the coordinates to fly to
            $this->dispatch('fly-to-point', [
                'latitude' => $point->latitude,
                'longitude' => $point->longitude,
                'radius' => $point->radius,
            ]);
        }
    }

    public function mount(): void
    {
        $this->radius = 75;
        $this->accuracy = 100;
        $this->points = [];
        $this->color = '#3b82f6';
    }

    #[Layout('components.layouts.guest')]
    #[Title('Plotter | WFM Toolkit')]
    public function render(): View
    {
        // Send all points to the view to initialize the map with existing points
        return view('livewire.tools.plotter', [
            'mapPoints' => $this->formatPointsForMap(),
        ]);
    }

    /**
     * Get all known places that contain the given punch within their boundaries
     * Returns array of known places with their distances
     */
    private function getKnownPlacesContainingPunch(Point $punch): array
    {
        $knownPlaces = array_filter($this->points, function ($point) {
            return $point->type === 'known_place';
        });

        if (empty($knownPlaces)) {
            return [];
        }

        $containingPlaces = [];

        foreach ($knownPlaces as $index => $knownPlace) {
            $distance = $this->calculateDistance(
                $punch->latitude,
                $punch->longitude,
                $knownPlace->latitude,
                $knownPlace->longitude
            );

            // Check if punch is within this known place's boundary
            if ($distance <= $knownPlace->radius) {
                $containingPlaces[] = [
                    'known_place' => $knownPlace,
                    'distance' => $distance,
                    'index' => $index,
                ];
            }
        }

        // Sort by distance (closest first)
        usort($containingPlaces, function ($a, $b) {
            return $a['distance'] <=> $b['distance'];
        });

        return $containingPlaces;
    }

    /**
     * Get all known places within a reasonable distance for accuracy comparison
     * This helps with accuracy validation even if punch is outside boundaries
     */
    private function getNearbyKnownPlaces(Point $punch, float $maxDistance = 500): array
    {
        $knownPlaces = array_filter($this->points, function ($point) {
            return $point->type === 'known_place';
        });

        if (empty($knownPlaces)) {
            return [];
        }

        $nearbyPlaces = [];

        foreach ($knownPlaces as $index => $knownPlace) {
            $distance = $this->calculateDistance(
                $punch->latitude,
                $punch->longitude,
                $knownPlace->latitude,
                $knownPlace->longitude
            );

            if ($distance <= $maxDistance) {
                $nearbyPlaces[] = [
                    'known_place' => $knownPlace,
                    'distance' => $distance,
                    'index' => $index,
                ];
            }
        }

        // Sort by distance (closest first)
        usort($nearbyPlaces, function ($a, $b) {
            return $a['distance'] <=> $b['distance'];
        });

        return $nearbyPlaces;
    }
}
