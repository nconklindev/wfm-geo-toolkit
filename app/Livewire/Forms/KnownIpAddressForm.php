<?php

namespace App\Livewire\Forms;

use App\Models\KnownIpAddress;
use App\Models\User;
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

    /**
     * Store method to create a new Known IP Address for the authenticated user
     *
     * @return void
     * @see KnownIpAddress, User
     */
    public function store(): void
    {
        $this->validate();

        auth()->user()->knownIpAddresses()->create($this->all());
    }

    public function update(): void
    {
        $this->validate();

        $this->knownIpAddress->update([$this->all(), 'user_id' => auth()->user()->id]);
    }
}
