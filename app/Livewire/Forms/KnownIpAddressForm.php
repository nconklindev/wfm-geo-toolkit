<?php

namespace App\Livewire\Forms;

use App\Models\KnownIpAddress;
use Livewire\Attributes\Validate;
use Livewire\Form;

class KnownIpAddressForm extends Form
{
    public ?KnownIpAddress $knownIpAddress;

    #[Validate(['required', 'ipv4'])]
    public $start = '';

    #[Validate(['required', 'ipv4'])]
    public $end = '';

    #[Validate(['required', 'string', 'min:3', 'max:255'])]
    public $name = '';

    #[Validate(['nullable', 'string', 'max:255', 'min:5'])]
    public $description = '';

    public function setKnownIpAddress(KnownIpAddress $knownIpAddress): void
    {
        $this->knownIpAddress = $knownIpAddress;
        $this->name = $knownIpAddress->name;
        $this->description = $knownIpAddress->description;
        $this->start = $knownIpAddress->start;
        $this->end = $knownIpAddress->end;
    }

    /**
     * Store method to create a new Known IP Address for the authenticated user
     */
    public function store(): void
    {
        $this->validate();

        auth()->user()->knownIpAddresses()->create($this->all());
    }

    public function update(): void
    {
        $this->validate();

        $this->knownIpAddress->update([
            'name' => $this->name,
            'description' => $this->description,
            'start' => $this->start,
            'end' => $this->end,
        ]);
    }
}
