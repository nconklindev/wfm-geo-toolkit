<?php

namespace App\Livewire\Notifications;

use Livewire\Component;

class Filters extends Component
{
    public string $currentFilter = 'all';
    public string $currentStatus = 'all';

    // TODO: Selecting a notification in the "Unread" category just marks it as read and doesn't display the details

    /**
     * Convert lowercase filter values to database format for proper matching
     */
    public function getStatusForDatabaseProperty(): string
    {
        return match ($this->currentStatus) {
            'critical' => 'Critical',
            'warning' => 'Warning',
            'info' => 'Info',
            'notification' => 'Notification',
            default => $this->currentStatus // 'all' and any other values pass through
        };
    }

    public function render()
    {
        return view('livewire.notifications.filters');
    }
}
