<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class SearchServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerSearchableModels();
    }

    private function registerSearchableModels(): void {}

    public function register(): void
    {
        //
    }
}
