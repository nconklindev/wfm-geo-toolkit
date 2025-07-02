<?php

namespace App\Livewire\Tools\ApiExplorer;

use App\Services\WfmService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Throwable;

class ApiExplorer extends Component
{
    // Credentials
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

    public ?string $errorMessage = null;

    protected WfmService $wfmService;

    public function boot(WfmService $wfmService)
    {
        $this->wfmService = $wfmService;

        // Load cached credentials from session
        $this->loadCredentialsFromSession();

        // Check if we have a stored token and credentials
        $this->checkExistingAuthentication();
    }

    private function loadCredentialsFromSession(): void
    {
        $credentials = session('wfm_credentials', []);

        $this->clientId = $credentials['client_id'] ?? '';
        // Don't load client_secret from session - force re-entry each time
        $this->orgId = $credentials['org_id'] ?? '';
        $this->username = $credentials['username'] ?? '';
        $this->hostname = $credentials['hostname'] ?? '';
        // Don't load password from session for security
    }

    private function checkExistingAuthentication(): void
    {
        // Check if we're marked as authenticated in session
        if (session('wfm_authenticated') && session('wfm_access_token')) {
            $this->isAuthenticated = true;

            // Set the token on the service but don't store in component property
            $this->wfmService->setAccessToken(session('wfm_access_token'));
            if ($this->hostname) {
                $this->wfmService->setHostname($this->hostname);
            }
        }
    }

    public function saveCredentials(): void
    {
        $this->validate([
            'clientId' => 'required|string',
            'clientSecret' => 'required|string',
            'orgId' => 'required|string',
            'username' => 'required|string',
            'password' => 'required|string',
            'hostname' => 'required|url',
        ]);

        // Save to session (excluding sensitive credentials)
        session([
            'wfm_credentials' => [
                'client_id' => $this->clientId,
                // Deliberately exclude client_secret
                'org_id' => $this->orgId,
                'username' => $this->username,
                'hostname' => $this->hostname,
                // Deliberately exclude password
            ],
        ]);

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

            // This calls the authenticate method from WfmService
            $success = $this->wfmService->authenticate(
                $this->clientId,
                $this->clientSecret,
                $this->orgId,
                $this->username,
                $this->password
            );

            if ($success) {
                $this->isAuthenticated = true;

                // Store authentication state and token in session (server-side only)
                session([
                    'wfm_authenticated' => true,
                    'wfm_access_token' => $this->wfmService->getAccessToken(),
                ]);
            } else {
                $this->isAuthenticated = false;

                // Clear session authentication
                session()->forget(['wfm_authenticated', 'wfm_access_token']);

                $this->errorMessage = 'Authentication failed. Please check your credentials.';
            }
        } catch (Throwable $e) {
            $this->isAuthenticated = false;

            // Clear session authentication
            session()->forget(['wfm_authenticated', 'wfm_access_token']);

            $this->errorMessage = 'Authentication error: '.$e->getMessage();
        } finally {
            $this->isLoading = false;
        }
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
        $this->loadCredentialsFromSession();
    }

    #[Layout('components.layouts.guest')]
    #[Title('API Explorer')]
    public function render()
    {
        return view('livewire.tools.api-explorer.api-explorer');
    }
}
