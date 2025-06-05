<?php

namespace App\Providers;

use App\Models\BusinessStructureNode;
use App\Models\KnownIpAddress;
use App\Models\KnownPlace;
use App\Services\SearchRegistryService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\ServiceProvider;

class SearchServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerSearchableModels();
    }

    private function registerSearchableModels(): void
    {
        // Register KnownPlace with Scout search optimization
        SearchRegistryService::register(
            modelClass: KnownPlace::class,
            routeName: 'known-places.show',
            routeParameter: 'knownPlace',
            displayName: 'Place',
            searchCallback: function (string $query, int $userId) {
                return KnownPlace::search($query)
                    ->where('user_id', $userId)
                    ->query(fn(Builder $query) => $query->with('group'))
                    ->get();
            }
        );

        // Register BusinessStructureNode
        SearchRegistryService::register(
            modelClass: BusinessStructureNode::class,
            routeName: 'locations.show',
            routeParameter: 'node',
            displayName: 'Location',
            searchCallback: function (string $query, int $userId) {
                return BusinessStructureNode::search($query)
                    ->where('user_id', $userId)
                    ->get();
            }
        );

        // Register KnownIpAddress
        SearchRegistryService::register(
            modelClass: KnownIpAddress::class,
            routeName: 'known-ip-addresses.index',
            routeParameter: null,
            displayName: 'IP Address',
            searchCallback: function (string $query, int $userId) {
                return KnownIpAddress::search($query)
                    ->where('user_id', $userId)
                    ->get();
            }
        );
    }

    public function register(): void
    {
        //
    }
}
