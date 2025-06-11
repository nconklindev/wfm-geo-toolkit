<?php

namespace App\Livewire;

use App\Livewire\Forms\KnownIpAddressForm;
use App\Models\KnownIpAddress;
use Livewire\Attributes\On;
use Livewire\Component;

class EditKnownIpAddressModal extends Component
{
    public KnownIpAddressForm $form;
    public ?KnownIpAddress $ipAddress = null;

    #[On('edit-known-ip-address')]
    public function edit($ipAddressId): void
    {
        logger()->debug('EditKnownIpAddressModal: Event received for IP address ID: '.$ipAddressId.' on component: '.$this->getId());

        $this->ipAddress = KnownIpAddress::find($ipAddressId);

        if ($this->ipAddress) {
            $this->form->setKnownIpAddress($this->ipAddress);
            logger()->debug('EditKnownIpAddressModal: Form data set: '.json_encode([
                    'name' => $this->form->name,
                    'description' => $this->form->description,
                    'start' => $this->form->start,
                    'end' => $this->form->end
                ]));
        } else {
            logger()->debug('EditKnownIpAddressModal: IP address not found');
        }
        $this->modal('edit-known-ip-address')->show();
    }

    public function mount()
    {
        logger()->debug('EditKnownIpAddressModal: Component mounted with ID: '.$this->getId());
        $this->form->reset();
    }

    public function render()
    {
        logger()->debug('EditKnownIpAddressModal: Rendering component '.$this->getId().' with ipAddress: '.($this->ipAddress ? $this->ipAddress->name : 'null'));
        return view('livewire.edit-known-ip-address-modal');
    }

    public function save()
    {
        $this->form->update();
        $this->modal('edit-known-ip-address')->close();
        return $this->redirect('/known-ip-addresses');
    }
}
