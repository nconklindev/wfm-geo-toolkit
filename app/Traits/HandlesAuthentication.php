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

    protected function setupAuthenticationFromToken(?string $accessToken = null): void
    {
        // If no token provided, try to get from session
        if (! $accessToken) {
            $accessToken = session('wfm_access_token');
        }

        // If no hostname set, try to get from session
        if (empty($this->hostname)) {
            $this->hostname = session('hostname', '');
        }

        if ($accessToken && ! empty($this->hostname)) {
            $this->isAuthenticated = true;
            $this->wfmService->setAccessToken($accessToken);
            $this->wfmService->setHostname($this->hostname);

            Log::info('Authentication setup completed', [
                'component' => get_class($this),
                'hostname' => $this->hostname,
                'isAuthenticated' => $this->isAuthenticated,
                'token_source' => $accessToken === session('wfm_access_token') ? 'session' : 'parameter',
            ]);
        } else {
            $this->isAuthenticated = false;
            Log::info('Authentication setup failed - no token or hostname', [
                'component' => get_class($this),
                'has_access_token' => ! empty($accessToken),
                'has_hostname' => ! empty($this->hostname),
                'session_token' => ! empty(session('wfm_access_token')),
                'session_hostname' => ! empty(session('hostname')),
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

        // Ensure hostname is set for the service
        if (! empty($this->hostname)) {
            $this->wfmService->setHostname($this->hostname);
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
        $this->isAuthenticated = false;
        $this->errorMessage = 'Your authentication has expired. Please re-authenticate to continue.';
    }
}
