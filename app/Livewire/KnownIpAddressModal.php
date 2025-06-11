<?php

namespace App\Livewire;

use App\Livewire\Forms\KnownIpAddressForm;
use Livewire\Component;

class KnownIpAddressModal extends Component
{
    public KnownIpAddressForm $form;


    public function render()
    {
        return view('livewire.known-ip-address-modal');
    }

    public function save()
    {
        $this->form->store();

        return $this->redirect('/known-ip-addresses');
    }
}
