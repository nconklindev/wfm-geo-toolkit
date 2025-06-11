<?php

namespace App\Services;

use App\Models\BusinessStructureNode;
use App\Models\KnownPlace;
use App\Notifications\KnownPlaceNotification;
use Illuminate\Support\Facades\Log;

class KnownPlaceValidationService
{
    /**
     * Validate a Known Place and send notification if issues are detected.
     */
    public function validateAndNotify(KnownPlace $knownPlace): void
    {
        $issues = $this->detectIssues($knownPlace);

        if (!empty($issues)) {
            $this->sendNotification($knownPlace, $issues);
        }
    }

    /**
     * Detect hierarchical conflicts in associated Known Places.
     */
    public function detectIssues(KnownPlace $knownPlace): array
    {
        $associatedNodes = $knownPlace->nodes;

        if ($associatedNodes->isEmpty()) {
            Log::info("KnownPlaceValidationService: KnownPlace ID {$knownPlace->id} has no associated nodes. Skipping conflict check.");
            return [];
        }

        Log::info("KnownPlaceValidationService: Checking conflicts for KnownPlace ID {$knownPlace->id} with nodes: ".$associatedNodes->pluck('id')->implode(', '));

        $conflictingDescendantPlaces = collect();
        $conflictingAncestorPlaces = collect();

        // Check each node individually for conflicts
        foreach ($associatedNodes as $node) {
            if (!($node instanceof BusinessStructureNode)) {
                continue;
            }

            // Get descendant and ancestor IDs for this specific node
            $descendantIds = $node->descendants()->pluck('id')->all();
            $ancestorIds = $node->ancestors()->pluck('id')->all();

            // Find conflicts for this specific node's descendants
            if (!empty($descendantIds)) {
                $descendantConflicts = KnownPlace::where('id', '!=', $knownPlace->id)
                    ->where('user_id', $knownPlace->user_id) // Only check within same user
                    ->whereHas('nodes', function ($query) use ($descendantIds) {
                        $query->whereIn('business_structure_node_id', $descendantIds);
                    })
                    ->with('nodes')
                    ->get();

                $conflictingDescendantPlaces = $conflictingDescendantPlaces->merge($descendantConflicts);
            }

            // Find conflicts for this specific node's ancestors
            if (!empty($ancestorIds)) {
                $ancestorConflicts = KnownPlace::where('id', '!=', $knownPlace->id)
                    ->where('user_id', $knownPlace->user_id) // Only check within same user
                    ->whereHas('nodes', function ($query) use ($ancestorIds) {
                        $query->whereIn('business_structure_node_id', $ancestorIds);
                    })
                    ->with('nodes')
                    ->get();

                $conflictingAncestorPlaces = $conflictingAncestorPlaces->merge($ancestorConflicts);
            }
        }

        // Remove duplicates
        $conflictingDescendantPlaces = $conflictingDescendantPlaces->unique('id');
        $conflictingAncestorPlaces = $conflictingAncestorPlaces->unique('id');

        $issues = [];
        if ($conflictingDescendantPlaces->isNotEmpty() || $conflictingAncestorPlaces->isNotEmpty()) {
            $severity = $this->determineSeverity($conflictingDescendantPlaces, $conflictingAncestorPlaces);

            $issues[] = [
                'type' => 'hierarchy_conflict',
                'severity' => $severity,
                'message' => $this->generateIssueMessage($severity, $conflictingDescendantPlaces,
                    $conflictingAncestorPlaces),
                'conflicting_descendant_places' => $conflictingDescendantPlaces->map(function ($place) {
                    return [
                        'id' => $place->id,
                        'name' => $place->name,
                        'nodes' => $place->nodes->map(function ($node) {
                            return [
                                'id' => $node->id,
                                'path' => $node->path,
                            ];
                        })->all(),
                    ];
                })->all(),
                'conflicting_ancestor_places' => $conflictingAncestorPlaces->map(function ($place) {
                    return [
                        'id' => $place->id,
                        'name' => $place->name,
                        'nodes' => $place->nodes->map(function ($node) {
                            return [
                                'id' => $node->id,
                                'path' => $node->path,
                            ];
                        })->all(),
                    ];
                })->all(),
            ];
        }

        return $issues;
    }

    /**
     * Send notification to the user about Known Place issues.
     */
    private function sendNotification(KnownPlace $knownPlace, array $issues): void
    {
        $highestSeverity = $this->getHighestSeverity($issues);

        $message = $this->generateNotificationMessage($issues);

        $issueDetails = [
            'message' => $message,
            'status' => $this->mapSeverityToStatus($highestSeverity),
            'details' => [
                'triggered_known_place' => [
                    'id' => $knownPlace->id,
                    'name' => $knownPlace->name,
                    'nodes' => $knownPlace->nodes->map(function ($node) {
                        return [
                            'id' => $node->id,
                            'path' => $node->path,
                        ];
                    })->all(),
                ],
                'issues' => $issues,
                'detected_at' => now()->toISOString(),
            ]
        ];

        $knownPlace->user->notify(new KnownPlaceNotification($knownPlace, $issueDetails));

        Log::info("Known Place validation notification sent", [
            'known_place_id' => $knownPlace->id,
            'user_id' => $knownPlace->user_id,
            'issues_count' => count($issues),
            'highest_severity' => $highestSeverity
        ]);
    }

    /**
     * Determine the severity level based on the type and extent of conflicts.
     */
    private function determineSeverity($conflictingDescendantPlaces, $conflictingAncestorPlaces): string
    {
        $descendantCount = $conflictingDescendantPlaces->count();
        $ancestorCount = $conflictingAncestorPlaces->count();
        $totalConflicts = $descendantCount + $ancestorCount;

        // If both types of conflicts exist, it's more critical
        if ($descendantCount > 0 && $ancestorCount > 0) {
            return 'critical';
        }

        // High number of conflicts in one category
        if ($totalConflicts >= 5) {
            return 'critical';
        }

        // Multiple conflicts or complex hierarchies
        if ($totalConflicts >= 2) {
            return 'warning';
        }

        // Single conflict - informational
        return 'info';
    }

    /**
     * Generate an issue-specific message.
     */
    private function generateIssueMessage(
        string $severity,
        $conflictingDescendantPlaces,
        $conflictingAncestorPlaces
    ): string {
        $descendantCount = $conflictingDescendantPlaces->count();
        $ancestorCount = $conflictingAncestorPlaces->count();

        if ($descendantCount > 0 && $ancestorCount > 0) {
            return "Hierarchy conflict: This Known Place conflicts with both broader and more specific locations";
        } elseif ($descendantCount > 0) {
            $pluralPlace = $descendantCount > 1 ? 'places' : 'place';
            return "Hierarchy conflict: This location is broader than {$descendantCount} other Known {$pluralPlace}";
        } elseif ($ancestorCount > 0) {
            $pluralPlace = $ancestorCount > 1 ? 'places' : 'place';
            return "Hierarchy conflict: This location is more specific than {$ancestorCount} other Known {$pluralPlace}";
        }

        return "Hierarchy conflict detected";
    }

    /**
     * Get the highest severity level from issues.
     */
    private function getHighestSeverity(array $issues): string
    {
        $severities = array_column($issues, 'severity');

        if (in_array('critical', $severities)) {
            return 'critical';
        } elseif (in_array('warning', $severities)) {
            return 'warning';
        } else {
            return 'info';
        }
    }

    /**
     * Generate a user-friendly notification message.
     */
    private function generateNotificationMessage(array $issues): string
    {
        $criticalIssues = array_filter($issues, fn($issue) => $issue['severity'] === 'critical');
        $warningIssues = array_filter($issues, fn($issue) => $issue['severity'] === 'warning');

        if (!empty($criticalIssues)) {
            return 'Critical hierarchy conflicts detected with your Known Place that require immediate attention.';
        } elseif (!empty($warningIssues)) {
            return 'Potential hierarchy conflicts detected with your Known Place that may need review.';
        } else {
            return 'Some hierarchy observations about your Known Place that you might want to review.';
        }
    }

    /**
     * Map severity to notification status.
     */
    private function mapSeverityToStatus(string $severity): string
    {
        return match ($severity) {
            'critical' => 'Critical',
            'warning' => 'Warning',
            'info' => 'Info',
            default => 'Notification',
        };
    }
}
