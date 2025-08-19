<?php

namespace App\Livewire\Tools\ApiExplorer;

use App\Services\WfmService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Throwable;

class ApiExplorer extends Component
{
    // Credentials
    // Flow configuration
    public string $flowType = 'interactive';

    public string $clientId = '';

    public string $clientSecret = '';

    public string $orgId = '';

    public string $username = '';

    public string $password = '';

    public string $hostname = '';

    // API Explorer state
    public ?string $selectedEndpoint = null;

    public ?string $selectedLabel = null;

    public bool $isAuthenticated = false;

    public ?array $apiResponse = null;

    public bool $isLoading = false;

    public bool $open = false;

    public ?string $errorMessage = null;


    protected WfmService $wfmService;

    // TODO: Add before validation that lets us trim the properties to account for accidental spaces

    public function boot(WfmService $wfmService): void
    {
        $this->wfmService = $wfmService;
    }



    public function saveCredentials(): void
    {
        // Build validation rules dynamically based on flow type
        $rules = [
            'clientId' => 'required|string|min:1',
            'clientSecret' => 'required|string|min:1',
            'orgId' => 'required|string',
            'hostname' => 'required|url',
        ];

        // Only validate username/password for interactive flow
        if ($this->flowType === 'interactive') {
            $rules['username'] = 'required|string';
            $rules['password'] = 'required|string';
        }

        // Clear any existing validation errors first
        $this->resetValidation();

        // Validate with the appropriate rules
        $this->validate($rules);

        // Try to authenticate
        $this->authenticate();

        $this->dispatch('credentials-saved');
    }

    public function authenticate(): void
    {
        $this->isLoading = true;
        $this->errorMessage = null;

        try {
            $this->wfmService->setHostname($this->hostname);

            // Use different authentication methods based on flow type
            if ($this->flowType === 'interactive') {
                $success = $this->wfmService->authenticate(
                    $this->clientId,
                    $this->clientSecret,
                    $this->orgId,
                    $this->username,
                    $this->password,
                );

                session(['hostname' => $this->hostname]);
            } else {
                // Non-interactive flow - use client credentials
                $success = $this->wfmService->authenticateNonInteractive(
                    $this->clientId,
                    $this->clientSecret,
                    $this->orgId,
                );
            }

            if ($success) {
                $this->isAuthenticated = true;
            } else {
                $this->isAuthenticated = false;
                $this->errorMessage = 'Authentication failed. Please check your credentials.';
            }
        } catch (Throwable $e) {
            $this->isAuthenticated = false;
            $this->errorMessage = 'Authentication error: '.$e->getMessage();
        } finally {
            $this->isLoading = false;
        }
    }

    public function logout(): void
    {
        // Clear authentication state
        $this->isAuthenticated = false;

        // Clear sensitive form data
        $this->clientSecret = '';
        $this->password = '';

        // Clear the access token from the service
        $this->wfmService->clearAccessToken();

        // Clear any API responses or error messages
        $this->apiResponse = null;
        $this->errorMessage = null;

        // Dispatch an event to notify other components
        $this->dispatch('wfm-logged-out');
        $this->dispatch('flow-type-changed');
    }

    public function selectEndpoint(string $endpoint, string $label): void
    {
        $this->selectedEndpoint = $endpoint;
        $this->selectedLabel = $label;

        // Reset any previous API responses when switching endpoints
        $this->apiResponse = null;
        $this->errorMessage = null;
    }

    public function mount(): void
    {
        // Component initialization - no session loading
    }

    #[Layout('components.layouts.guest')]
    #[Title('API Explorer')]
    public function render(): View
    {
        return view('livewire.tools.api-explorer.api-explorer', [
            'wfmService' => $this->wfmService,
        ]);
    }

    public function updatedClientId(): void
    {
        $this->clientId = trim($this->clientId);
    }

    public function updatedClientSecret(): void
    {
        $this->clientSecret = trim($this->clientSecret);
    }

    public function updatedOrgId(): void
    {
        $this->orgId = trim($this->orgId);
    }

    public function updatedUsername(): void
    {
        $this->username = trim($this->username);
    }

    public function updatedPassword(): void
    {
        $this->password = trim($this->password);
    }

    public function updatedFlowType(): void
    {
        // When flow type changes, clear authentication and reset relevant fields
        $wasAuthenticated = $this->isAuthenticated;

        if ($this->isAuthenticated) {
            $this->logout();
        }

        // Reset flow-specific fields
        if ($this->flowType !== 'interactive') {
            // Clear username/password for non-interactive flow
            $this->username = '';
            $this->password = '';
        }

        // Clear any error messages and validation errors
        $this->errorMessage = null;
        $this->resetValidation();

        // Dispatch event if user was previously authenticated
        if ($wasAuthenticated) {
            $this->dispatch('flow-type-changed');
        }
    }

}
