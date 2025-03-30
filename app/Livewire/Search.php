<?php

namespace App\Livewire;

use App\Models\KnownPlace;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Search extends Component
{

    #[Validate('string')]
    public string $searchQuery = '';
    public Collection $results;

    public function updatedSearchQuery(): void
    {
        $this->validate();
        
        $this->results = KnownPlace::search($this->searchQuery)->where('user_id', auth()->id())->get();
    }

    public function render()
    {
        return view('livewire.search');
    }
}
