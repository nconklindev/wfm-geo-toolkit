<?php

namespace App\Livewire;

use App\Services\KnownPlaceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;
use Livewire\WithFileUploads;

class ImportKnownPlaces extends Component
{

    use WithFileUploads;

    #[Validate(['required', 'file', 'extensions:json', 'mimes:json', 'max:10240'])]
    public $file = null;

    #[Validate(['required', 'in:skip,update,replace'])]
    public string $duplicateHandling = 'skip';

    #[Validate(['required', 'string', 'in:name,coordinates,both'])]
    public string $matchBy = 'name';

    #[Validate(['required', 'boolean'])]
    public bool $includeInactive = false;

    protected KnownPlaceService $knownPlaceService;

    public function boot(KnownPlaceService $knownPlaceService): void
    {
        $this->knownPlaceService = $knownPlaceService;
    }

    public function import(Request $request): RedirectResponse|Redirector
    {
        $this->validate();

        $result = $this->knownPlaceService->processUploadedFile(
            $this->file,
            auth()->user(),
            [
                'duplicate_handling' => $request->input('duplicate_handling', 'skip'),
                'match_by' => $request->input('match_by', 'name'),
                'include_inactive' => $request->boolean('include_inactive'),
            ]
        );

        if (!$result['success']) {
            return redirect()->back()->withErrors(['file' => $result['message']]);
        }

        flash()
            ->option('position', 'bottom-right')
            ->option('timeout', 5000)
            ->success($result['message']);

        return redirect()->route('known-places.create');
    }

    #[Layout('components.layouts.app')]
    #[Title('Import Known Places')]
    public function render()
    {
        return view('livewire.import-known-places');
    }
}
