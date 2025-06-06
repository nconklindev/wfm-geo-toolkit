<?php

namespace App\Livewire;

use Exception;
use Livewire\Attributes\Validate;
use Livewire\Component;

class CreateGroupModal extends Component
{
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('nullable|string|max:500')]
    public string $description = '';

    public bool $isCreating = false;

    public function createGroup()
    {
        $this->isCreating = true;

        try {
            $this->validate();

            // TODO: Replace with your actual Group model creation
            auth()->user()->groups()->create([
                'name' => $this->name,
                'description' => $this->description,
            ]);

            session()->flash('success', "Group '{$this->name}' created successfully!");

            $this->resetForm();
            $this->modal('create-group-modal')->close();

            // Optionally dispatch an event to refresh parent components
            $this->dispatch('group-created', [
                'name' => $this->name,
                'description' => $this->description
            ]);

        } catch (Exception $e) {
            session()->flash('error', 'Failed to create group: '.$e->getMessage());
        } finally {
            $this->isCreating = false;
        }

        return $this->redirectIntended('/dashboard', navigate: true);
    }

    public function resetForm(): void
    {
        $this->name = '';
        $this->description = '';
        $this->resetValidation();
    }

    public function mount(): void
    {
        $this->resetForm();
    }

    public function render()
    {
        return view('livewire.create-group-modal');
    }
}
