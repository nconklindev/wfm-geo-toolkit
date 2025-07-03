<?php

namespace App\Livewire\Tools\ApiExplorer\Endpoints;

use App\Livewire\Tools\ApiExplorer\BaseApiEndpoint;
use Livewire\Attributes\Validate;
use stdClass;

class LaborCategoriesList extends BaseApiEndpoint
{
    #[Validate('required|string|max:255')]
    public string $name = '';

    public function executeRequest(): void
    {
        $this->executeApiCall();
    }

    public function render()
    {
        return view('livewire.tools.api-explorer.endpoints.labor-categories-list');
    }

    protected function initializeEndpoint(): void
    {
        // Initialize with empty form
    }

    protected function makeApiCall()
    {
        // Build request data based on form input
        $requestData = ['where' => new stdClass];

        // If name is provided, add it to the request
        if (! empty($this->name)) {
            $requestData['where'] = [
                'entries' => [
                    'qualifiers' => [$this->name],
                ],
            ];
        }

        return $this->wfmService->getLaborCategoryEntries($requestData);
    }
}
