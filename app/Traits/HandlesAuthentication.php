<?php

namespace App\Traits;

use App\Services\WfmService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;

trait HandlesAuthentication
{
    public bool $isAuthenticated = false;

    public string $hostname = '';

    protected string $errorMessage = '';

    protected WfmService $wfmService;

    protected function setupAuthenticationFromSession(): void
    {
        // FIXME: If user logs out and logs back in, same session is being used which is loading the cached data
        // Do we want that behavior?
        if (session('wfm_authenticated') && session('wfm_access_token')) {
            $this->isAuthenticated = true;
            $this->wfmService->setAccessToken(session('wfm_access_token'));

            // Always try to get hostname from session first, then fall back to the component property
            $sessionHostname = session('wfm_credentials.hostname');

            if (! empty($sessionHostname)) {
                $this->wfmService->setHostname($sessionHostname);
                $this->hostname = $sessionHostname;
            } elseif (! empty($this->hostname)) {
                $this->wfmService->setHostname($this->hostname);
            }

            Log::info('Authentication setup completed', [
                'component' => get_class($this),
                'hostname' => $this->hostname,
                'session_hostname' => $sessionHostname,
                'isAuthenticated' => $this->isAuthenticated,
            ]);
        } else {
            $this->isAuthenticated = false;
            Log::info('Authentication setup failed - no session data', [
                'component' => get_class($this),
                'wfm_authenticated' => session('wfm_authenticated'),
                'has_access_token' => ! empty(session('wfm_access_token')),
            ]);
        }
    }

    protected function makeAuthenticatedApiCall(callable $apiCallFunction): ?Response
    {
        if (! $this->isAuthenticated) {
            Log::warning('API call attempted without authentication', [
                'component' => get_class($this),
            ]);

            return null;
        }

        // Double-check hostname is set before making the call
        if (empty($this->hostname)) {
            $sessionHostname = session('wfm_credentials.hostname');
            if (! empty($sessionHostname)) {
                $this->hostname = $sessionHostname;
                $this->wfmService->setHostname($sessionHostname);
            }
        }

        if (empty($this->hostname)) {
            $this->errorMessage = 'Hostname is required for API calls. Please re-authenticate.';

            return null;
        }

        try {
            $response = $apiCallFunction();

            if (! $this->validateAuthenticationState($response)) {
                return null;
            }

            return $response;
        } catch (ConnectionException $e) {
            $this->errorMessage = 'Unable to connect to API. Please check your network connection and try again.';
            Log::error('Connection error in API call', [
                'error' => $e->getMessage(),
                'component' => get_class($this),
                'hostname' => $this->hostname,
            ]);

            return null;
        }
    }

    protected function validateAuthenticationState($response): bool
    {
        if ($response instanceof Response) {
            $statusCode = $response->status();
            if ($statusCode === 401 || $statusCode === 403) {
                $this->handleAuthenticationFailure();

                return false;
            }
        } elseif (is_array($response) && isset($response['status']) &&
            ($response['status'] === 401 || $response['status'] === 403)) {
            $this->handleAuthenticationFailure();

            return false;
        }

        return true;
    }

    protected function handleAuthenticationFailure(): void
    {
        session()?->forget(['wfm_authenticated', 'wfm_access_token']);
        $this->isAuthenticated = false;
        $this->errorMessage = 'Your authentication session has expired. Please re-enter your credentials to continue.';
    }
}
