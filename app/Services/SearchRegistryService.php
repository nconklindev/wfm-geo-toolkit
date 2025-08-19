<?php

namespace App\Services;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class SearchRegistryService
{
    private static array $searchableModels = [];

    /**
     * Get route configuration for a model
     */
    public static function getRouteConfig(string $modelType): array
    {
        $config = self::getModelConfig($modelType);

        if (! $config) {
            throw new Exception("Model type '{$modelType}' is not registered as searchable");
        }

        return [
            'route_name' => $config['route_name'],
            'route_parameter' => $config['route_parameter'],
            'has_parameter' => ! is_null($config['route_parameter']),
        ];
    }

    /**
     * Get configuration for a specific model type
     */
    public static function getModelConfig(string $modelType): ?array
    {
        return self::$searchableModels[$modelType] ?? null;
    }

    /**
     * Get all registered searchable models
     */
    public static function getSearchableModels(): array
    {
        return self::$searchableModels;
    }

    /**
     * Check if a model type is registered
     */
    public static function isRegistered(string $modelType): bool
    {
        return isset(self::$searchableModels[$modelType]);
    }

    /**
     * Register a model as searchable
     */
    public static function register(
        string $modelClass,
        string $routeName,
        ?string $routeParameter,
        string $displayName,
        ?callable $searchCallback = null,
        ?callable $displayCallback = null
    ): void {
        self::$searchableModels[class_basename($modelClass)] = [
            'class' => $modelClass,
            'route_name' => $routeName,
            'route_parameter' => $routeParameter,
            'display_name' => $displayName,
            'search_callback' => $searchCallback,
            'display_callback' => $displayCallback,
        ];
    }

    /**
     * Search across all registered models using Scout/Algolia
     * Returns an Eloquent Collection to maintain type compatibility
     */
    public static function searchAll(string $query, int $userId): Collection
    {
        $allResults = new EloquentCollection;

        foreach (self::$searchableModels as $config) {
            $modelClass = $config['class'];

            if (! class_exists($modelClass)) {
                continue;
            }

            try {
                if ($config['search_callback']) {
                    $results = call_user_func($config['search_callback'], $query, $userId);
                } else {
                    // Default Scout search with Algolia filtering
                    $results = $modelClass::search($query)
                        ->where('user_id', $userId)
                        ->get();
                }

                // Merge the Eloquent collections properly
                $allResults = $allResults->merge($results);
            } catch (Exception $e) {
                // Log the error but continue with other models
                logger()->warning("Search failed for model {$modelClass}: ".$e->getMessage());
            }
        }

        return $allResults->sortBy('name')->values();
    }
}
