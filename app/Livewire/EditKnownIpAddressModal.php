<?php

namespace App\Livewire;

use App\Livewire\Forms\KnownIpAddressForm;
use App\Models\KnownIpAddress;
use Livewire\Component;

class EditKnownIpAddressModal extends Component
{
    public KnownIpAddressForm $form;
    public KnownIpAddress $ipAddress;

    public function mount(KnownIpAddress $ipAddress)
    {
        $this->ipAddress = $ipAddress;
        $this->form->setKnownIpAddress($this->ipAddress);
    }

    public function render()
    {
        return view('livewire.edit-known-ip-address-modal');
    }

    public function save()
    {
        $this->form->update();

        // Redirect to refresh the page and show updated data
        return $this->redirect('/known-ip-addresses');
    }
}
