<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class WfmService
{
    // Token URL mappings
    private string $accessToken = '';

    // Default token URL if no mapping is found
    private string $hostname = '';

    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Authenticate with WFM API via Auth0
     *
     * @param  string  $clientId
     * @param  string  $clientSecret
     * @param  string  $orgId
     * @param  string  $username
     * @param  string  $password
     *
     * @return bool
     */
    public function authenticate(
        string $clientId,
        string $clientSecret,
        string $orgId,
        string $username,
        string $password
    ): bool {
        $grantType = 'http://auth0.com/oauth/grant-type/password-realm';
        $audience = 'https://wfm.ukg.net/api';
        $tokenUrl = $this->getTokenUrl();

        // Get the authenticated user and IP address
        $appUsername = Auth::check() ? Auth::user()->username : 'Guest';
        $ipAddress = $this->request->ip();

        Log::info('Authenticating with WFM', [
            'tokenUrl' => $tokenUrl,
            'hostname' => $this->hostname,
            'app_user' => $appUsername,
            'ip_address' => $ipAddress,
            'client_username' => $username,
        ]);

        try {
            $response = Http::asForm()->withHeaders([
                'Accept' => '*/*',
                'Connection' => 'keep-alive',
            ])->post($tokenUrl, [
                'grant_type' => $grantType,
                'audience' => $audience,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'username' => $username,
                'password' => $password,
                'realm' => $orgId,
            ]);

            if ($response->successful()) {
                $this->accessToken = $response->json('access_token');

                Log::info('WFM Authentication successful', [
                    'app_user' => $appUsername,
                    'ip_address' => $ipAddress,
                ]);

                return true;
            }

            Log::error('WFM Authentication failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'app_user' => $appUsername,
                'ip_address' => $ipAddress,
            ]);

            return false;
        } catch (Throwable $e) {
            Log::error('WFM Authentication exception', [
                'message' => $e->getMessage(),
                'app_user' => $appUsername,
                'ip_address' => $ipAddress,
            ]);

            return false;
        }
    }

    /**
     * Get the appropriate token URL based on the hostname
     */
    public function getTokenUrl(): string
    {
        if (empty($this->hostname)) {
            return $this->getEvalTokenUrl();
        }

        // Extract the base URL (scheme + host)
        $parsedUrl = parse_url($this->hostname);
        if (! $parsedUrl || ! isset($parsedUrl['scheme']) || ! isset($parsedUrl['host'])) {
            return $this->getEvalTokenUrl();
        }

        // If the hostname contains 'cfn', use the evaluation endpoint
        if (stripos($parsedUrl['host'], 'cfn') !== false) {
            return $this->getEvalTokenUrl();
        }

        // Otherwise, use the production endpoint
        return $this->getProdTokenUrl();
    }

    /**
     * Get the Eval Token URL from Config
     */
    private function getEvalTokenUrl(): string
    {
        return Config::get('wfm.token_urls.eval');
    }

    /**
     * Get the Prod Token URL from Config
     */
    private function getProdTokenUrl(): string
    {
        return Config::get('wfm.token_urls.prod');
    }

    /**
     * Create a new known place in WFM using its API
     *
     * @param  array  $placeData
     *
     * @return Response
     * @throws ConnectionException
     */
    public function createKnownPlace(array $placeData): Response
    {
        // Get the authenticated user and IP address for logging
        $appUsername = Auth::check() ? Auth::user()->username : 'Guest';
        $ipAddress = $this->request->ip();

        // Log the request details for debugging
        Log::info('WFM Create Known Place - Request Debug', [
            'hostname' => $this->hostname,
            'endpoint' => "{$this->hostname}/api/v1/commons/known_places",
            'app_user' => $appUsername,
            'ip_address' => $ipAddress,
            'request_data' => $placeData,
            'request_data_json' => json_encode($placeData, JSON_PRETTY_PRINT),
            'data_types' => $this->getDataTypes($placeData),
            'has_token' => ! empty($this->accessToken),
            'token_length' => strlen($this->accessToken),
        ]);

        $response = Http::withToken($this->accessToken)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
            ->post("{$this->hostname}/api/v1/commons/known_places", $placeData);

        // Log the response for debugging
        Log::info('WFM Create Known Place - Response Debug', [
            'status' => $response->status(),
            'headers' => $response->headers(),
            'body' => $response->body(),
            'json' => $response->json(),
            'app_user' => $appUsername,
            'ip_address' => $ipAddress,
        ]);

        return $response;
    }

    /**
     * Helper method to analyze data types in the request
     */
    private function getDataTypes(array $data): array
    {
        $types = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $types[$key] = 'array';
                foreach ($value as $index => $item) {
                    if (is_array($item)) {
                        foreach ($item as $subKey => $subValue) {
                            $types[$key."[$index][$subKey]"] = gettype($subValue).' ('.json_encode($subValue).')';
                        }
                    } else {
                        $types[$key."[$index]"] = gettype($item).' ('.json_encode($item).')';
                    }
                }
            } else {
                $types[$key] = gettype($value).' ('.json_encode($value).')';
            }
        }

        return $types;
    }

    /**
     * Extract place IDs from a list of places
     *
     * @param  array  $places
     *
     * @return array
     */
    public function extractPlaceIds(array $places): array
    {
        return array_map(function ($place) {
            return $place['id'];
        }, $places);
    }

    /**
     * Get all known places from the WFM using its API
     *
     * @return array
     */
    public function getKnownPlaces(): array
    {
        try {
            $response = Http::withToken($this->accessToken)
                ->get("{$this->hostname}/api/v1/commons/known_places");

            Log::debug('WFM API response', [$response]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Failed to get known places', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [];
        } catch (Throwable $e) {
            Log::error('Exception when getting known places', [
                'message' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Find the next available ID from existing place IDs
     */
    public function getNextAvailableId(array $placeIds, int $startFrom = 1): int
    {
        sort($placeIds);
        $nextId = $startFrom;

        foreach ($placeIds as $id) {
            if ($id > $nextId) {
                break;
            }
            $nextId = $id + 1;
        }

        return $nextId;
    }

    public function handleWfmError(Response $response, string $context = 'WFM API error'): array
    {
        // Get the authenticated user and IP address for logging
        $appUsername = Auth::check() ? Auth::user()->username : 'Guest';
        $ipAddress = $this->request->ip();

        // Extract error details from response
        $status = $response->status();
        $body = $response->body();
        $errorData = $response->json() ?? [];

        // Attempt to extract more detailed error info
        $errorMessage = $errorData['message'] ?? $errorData['error'] ?? 'Unknown error';
        $errorCode = $errorData['code'] ?? $status;

        // Compile a structured error message
        $message = "$context failed: $errorMessage";

        // Log the error with detailed context
        Log::error($message, [
            'status' => $status,
            'body' => $body,
            'app_user' => $appUsername,
            'ip_address' => $ipAddress,
            'hostname' => $this->hostname,
        ]);

        // Return a structured error result
        return [
            'success' => false,
            'message' => $message,
            'code' => $errorCode,
            'details' => $errorData,
        ];
    }

    /**
     * Set the hostname for API calls
     */
    public function setHostname(string $hostname): void
    {
        $this->hostname = $hostname;
    }

    /**
     * Get the current access token
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    /**
     * Set the access token (useful for restoring from session)
     */
    public function setAccessToken(string $token): void
    {
        $this->accessToken = $token;
    }
}
