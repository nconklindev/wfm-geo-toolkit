<?php

namespace App\Observers;

use App\Models\KnownIpAddress;
use App\Services\IpAddressValidationService;

class KnownIpAddressObserver
{
    public function __construct(
        private readonly IpAddressValidationService $validationService
    ) {
    }

    /**
     * Handle the KnownIpAddress "created" event.
     */
    public function created(KnownIpAddress $knownIpAddress): void
    {
        $this->validationService->validateAndNotify($knownIpAddress);
    }

    /**
     * Handle the KnownIpAddress "updated" event.
     */
    public function updated(KnownIpAddress $knownIpAddress): void
    {
        // Only validate if start or end IP changed
        if ($knownIpAddress->wasChanged(['start', 'end'])) {
            $this->validationService->validateAndNotify($knownIpAddress);
        }
    }
}
