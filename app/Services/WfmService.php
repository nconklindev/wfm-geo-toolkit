<?php

namespace App\Services;

use App\Livewire\Tools\ApiExplorer\Endpoints\LaborCategoriesPaginatedList;
use GuzzleHttp\Promise\PromiseInterface;
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

        // Extract the base URL (scheme plus host)
        $parsedUrl = parse_url($this->hostname);
        if (! $parsedUrl || ! isset($parsedUrl['scheme']) || ! isset($parsedUrl['host'])) {
            return $this->getEvalTokenUrl();
        }

        // If the hostname contains 'cfn', use the evaluation endpoint
        if (stripos($parsedUrl['host'], 'cfn') !== false) {
            Log::debug('CFN Host Detected', [
                'hostname' => $this->hostname,
                'parsed_url' => $parsedUrl,
                'token_url' => $this->getEvalTokenUrl(),
            ]);

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
     *
     * @throws ConnectionException
     */
    public function createKnownPlace(array $placeData): Response
    {
        // Get the authenticated user and IP address for logging
        $appUsername = Auth::check() ? Auth::user()->username : 'Guest';
        $ipAddress = $this->request->ip();
        $apiPath = 'api/v1/commons/known_places';

        // Log the request details for debugging
        Log::info('WFM Create Known Place - Request Info', [
            'hostname' => $this->hostname,
            'endpoint' => "$this->hostname$apiPath",
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
            ->post("$this->hostname/$apiPath", $placeData);

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
     * Returns a list of paycodes available to a manager
     *
     * - When this operation is executed with no parameters, the system returns a list of all available paycodes to the manager.
     * - When this operation includes an ID or qualifier, the system returns a list containing the specified paycode, if available to the manager.
     *
     * @see https://developer.ukg.com/wfm/reference/retrieve-paycodes-as-manager
     *
     * @return Response the response from the Pro WFM API containing the JSON data
     *
     * @throws ConnectionException
     */
    public function getPaycodes(array $requestData = []): Response
    {
        $appUsername = Auth::check() ? Auth::user()->username : 'Guest';
        $ipAddress = $this->request->ip();
        $apiPath = 'api/v2/timekeeping/setup/pay_codes';

        // Log the request details for debugging
        Log::info('WFM Get Paycodes - Request Info', [
            'hostname' => $this->hostname,
            'endpoint' => "$this->hostname/$apiPath",
            'app_user' => $appUsername,
            'ip_address' => $ipAddress,
        ]);

        try {
            $response = Http::withToken($this->accessToken)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->get("$this->hostname/$apiPath");

        } catch (ConnectionException $ce) {
            Log::error('WFM Connection Error', [
                'error' => $ce->getMessage(),
                'hostname' => $this->hostname,
                'app_user' => $appUsername,
                'ip_address' => $ipAddress,
                'request_data' => $requestData,
            ]);

            throw $ce;
        }

        return $response;
    }

    /**
     * Extract place IDs from a list of places
     */
    public function extractPlaceIds(array $places): array
    {
        return array_map(function ($place) {
            return $place['id'];
        }, $places);
    }

    /**
     * Get all known places from the WFM using its API
     */
    public function getKnownPlaces(): array
    {
        try {
            $response = Http::withToken($this->accessToken)
                ->get("$this->hostname/api/v1/commons/known_places");

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

    /**
     * @throws ConnectionException
     */
    public function getAdjustmentRules(array $requestData = []): Response
    {
        $appUsername = Auth::check() ? Auth::user()->username : 'Guest';
        $ipAddress = $this->request->ip();
        $apiPath = 'api/v1/timekeeping/setup/adjustment_rules';

        Log::info('WFM Adjustment Rules - Request Info', [
            'hostname' => $this->hostname,
            'endpoint' => "$this->hostname/$apiPath",
            'app_user' => $appUsername,
            'ip_address' => $ipAddress,
            'request_data' => $requestData,
        ]);

        try {
            $response = Http::withToken($this->accessToken)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->get("$this->hostname/$apiPath");
        } catch (ConnectionException $e) {
            Log::error('WFM Connection Error', [
                'error' => $e->getMessage(),
                'hostname' => $this->hostname,
                'app_user' => $appUsername,
                'ip_address' => $ipAddress,
                'request_data' => $requestData,
            ]);

            throw $e;
        }

        return $response;
    }

    /**
     * Gets the paginated list of Labor Category Entries using the provided Labor Category name
     *
     * @param  array  $requestData  request parameters
     * @return Response the JSON response from the WFM API
     *
     * @throws ConnectionException
     *
     * @see https://developer.ukg.com/wfm/reference/retrieve-paginated-list-of-labor-category-entries
     */
    public function getLaborCategoryEntriesPaginated(array $requestData = []): Response
    {
        // Get the authenticated user and IP address for logging
        $appUsername = Auth::check() ? Auth::user()->username : 'Guest';
        $ipAddress = $this->request->ip();

        $apiPath = 'api/v1/commons/labor_entries/apply_read';

        // Log the request details for debugging
        Log::info('WFM Labor Category Entries Paginated - Request Info', [
            'hostname' => $this->hostname,
            'endpoint' => "$this->hostname/$apiPath",
            'app_user' => $appUsername,
            'ip_address' => $ipAddress,
            'request_data' => $requestData,
        ]);

        try {
            $response = Http::withToken($this->accessToken)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post("$this->hostname/$apiPath", $requestData);

            Log::debug('WFM Labor Category Entries Paginated - Response Debug', [
                'status' => $response->status(),
                'headers' => $response->headers(),
                'body' => $response->body(),
                'json' => $response->json(),
            ]);
        } catch (ConnectionException $e) {
            Log::error('WFM Connection Error', [
                'error' => $e->getMessage(),
                'hostname' => $this->hostname,
                'app_user' => $appUsername,
                'ip_address' => $ipAddress,
            ]);

            throw $e;
        }

        return $response;
    }

    /**
     * Clear the access token (used for logout)
     */
    public function clearAccessToken(): void
    {
        $this->accessToken = '';
    }

    /**
     * Get labor category entries from WFM using its API
     *
     * @param  array  $requestData  Optional request parameters/filters
     *
     * @throws ConnectionException
     */
    public function getLaborCategoryEntries(array $requestData = []): Response
    {
        // Get the authenticated user and IP address for logging
        $appUsername = Auth::check() ? Auth::user()->username : 'Guest';
        $ipAddress = $this->request->ip();

        $apiPath = 'api/v1/commons/labor_entries/multi_read';
        try {
            $response = Http::withToken($this->accessToken)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post("$this->hostname/$apiPath", $requestData);
        } catch (ConnectionException $e) {
            Log::error('WFM Connection Error', [
                'error' => $e->getMessage(),
                'hostname' => $this->hostname,
                'app_user' => $appUsername,
                'ip_address' => $ipAddress,
            ]);

            throw $e;
        }

        return $response;
    }

    /**
     * Get the list of available Labor Categories from WFM
     *
     * Used to populate the list of available Labor Categories in other endpoints
     *
     * @see LaborCategoriesPaginatedList
     * @see https://developer.ukg.com/wfm/reference/retrieve-all-labor-categories-or-by-criteria
     *
     * @return Response the JSON response from the API
     *
     * @throws ConnectionException
     */
    public function getLaborCategories(): Response
    {
        // Get the authenticated user and IP Address for logging
        $appUsername = Auth::check() ? Auth::user()->username : 'Guest';
        $ipAddress = $this->request->ip();
        $apiPath = 'api/v1/commons/labor_categories';

        try {
            $response = Http::withToken($this->accessToken)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->get("$this->hostname/$apiPath");
        } catch (ConnectionException $ce) {
            Log::error('WFM Connection Error', [
                'error' => $ce->getMessage(),
                'hostname' => $this->hostname,
                'app_user' => $appUsername,
                'ip_address' => $ipAddress,
            ]);

            // Re-throw the exception so it cna be caught by the parent calling method
            throw $ce;
        }

        return $response;
    }

    /**
     * Get the JSON representation of all data elements from WFM
     *
     * @return Response|PromiseInterface the response from the API containing the data
     *
     * @throws ConnectionException
     */
    public function getDataElementsPaginated(): PromiseInterface|Response
    {
        $appUsername = Auth::check() ? Auth::user()->username : 'Guest';
        $ipAddress = $this->request->ip();
        $apiPath = 'api/v1/commons/data_dictionary/data_elements';

        try {
            $response = Http::withToken($this->accessToken)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->get("$this->hostname/$apiPath");
        } catch (ConnectionException $ce) {
            Log::error('WFM Connection Error', [
                'error' => $ce->getMessage(),
                'hostname' => $this->hostname,
                'app_user' => $appUsername,
                'ip_address' => $ipAddress,
            ]);

            throw $ce;
        }

        return $response;
    }
}
