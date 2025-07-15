<?php

namespace App\Services;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use JsonException;
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
     * Retrieve paginated locations from the WFM using its API.
     *
     * @throws \Illuminate\Http\Client\ConnectionException|\JsonException
     */
    public function getLocationsPaginated(array $requestData = []): Response|array
    {
        return $this->callWfmApi(
            'POST',
            'api/v1/commons/locations/multi_read',
            $requestData,
        );
    }

    /**
     * Makes a call to the WFM API with the specified method, endpoint, data,
     * and headers.
     *
     * This method logs the API request details, including hostname, endpoint,
     * client information, and provided headers, before executing the HTTP
     * request. It supports both GET and POST methods and allows handling JSON
     * and non-JSON payloads.
     *
     * If the API call fails, it logs any errors related to connection or JSON
     * parsing and can delegate error handling for unsuccessful HTTP responses.
     *
     * @param  string  $method  The HTTP method to use for the API call (e.g.,
     *                          'GET', 'POST').
     * @param  string  $apiPath  The API endpoint path to append to the base
     *                           hostname.
     * @param  array  $data  The request payload to send.
     * @param  array  $headers  Additional headers to include in the request.
     * @param  bool  $asJson  Indicates whether to encode the payload as JSON.
     * @return Response|array Returns the API response as a Response object or
     *                        an array.
     *
     * @throws ConnectionException If there is an error connecting to the API.
     * @throws JsonException If there is an error with encoding or decoding the
     *                       JSON payload.
     */
    private function callWfmApi(
        string $method,
        string $apiPath,
        array $data = [],
        array $headers = [],
        bool $asJson = true,
    ): Response|array {
        $appUsername = Auth::check() ? Auth::user()->username : 'Guest';
        $ipAddress = $this->request->ip();
        $url = rtrim($this->hostname, '/').'/'.ltrim($apiPath, '/');
        $defaultHeaders = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
        $headers = array_merge($defaultHeaders, $headers);

        Log::info('WFM API Call', [
            'hostname' => $this->hostname,
            'endpoint' => $url,
            'app_user' => $appUsername,
            'ip_address' => $ipAddress,
            'method' => $method,
            'data' => $data,
            'headers' => $headers,
        ]);

        try {
            $http = Http::withToken($this->accessToken)->withHeaders($headers);

            if (strtolower($method) === 'get') {
                $response = $http->get($url, $data);
            } else {
                $response = $http->post(
                    $url,
                    $asJson ? $data : json_encode($data, JSON_THROW_ON_ERROR),
                );
            }

            if (! $response->successful()) {
                return $this->handleWfmError($response, "WFM API: $apiPath");
            }

            return $response;
        } catch (ConnectionException $ce) {
            Log::error('WFM Connection Error', [
                'error' => $ce->getMessage(),
                'url' => $url,
            ]);
            throw $ce;
        } catch (JsonException $e) {
            Log::error('WFM JSON Error', [
                'error' => $e->getMessage(),
                'url' => $url,
                'data' => $data,
            ]);
            throw $e;
        }
    }

    /**
     * Handle errors returned by the WFM API and generate a structured
     * response.
     *
     * @param  Response  $response  The response object from the WFM API.
     * @param  string|null  $context  A contextual description of the error
     *                                (default: 'WFM API error').
     * @return array A structured array containing error details, including
     *               success status, message, code, and additional details.
     */
    private function handleWfmError(
        Response $response,
        ?string $context = null,
    ): Response {
        // Get the authenticated user and IP address for logging
        $appUsername = Auth::check() ? Auth::user()->username : 'Guest';
        $ipAddress = $this->request->ip();

        // Extract error details from response
        $status = $response->status();
        $body = $response->body();
        $errorData = $response->json() ?? [];

        // Attempt to extract more detailed error info
        $errorMessage = $errorData['message'] ??
            $errorData['error'] ?? 'Unknown error';
        $errorCode = $errorData['code'] ?? $status;

        // Check if the passed context is null
        // Set the context to the method that called this
        // which will give the API endpoint method that was called and failed
        if ($context === null) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
            $context = $trace[1]['function'] ?? 'WFM API error';
        }

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

        // Return the original response
        return $response;
    }

    /**
     * Authenticate with the WFM API using the provided credentials.
     *
     * @param  string  $clientId  The client ID for the API.
     * @param  string  $clientSecret  The client secret for the API.
     * @param  string  $orgId  The organization ID (realm) for authentication.
     * @param  string  $username  The username of the client accessing the API.
     * @param  string  $password  The password of the client accessing the API.
     * @return bool Returns true if authentication is successful, otherwise
     *              false.
     */
    public function authenticate(
        string $clientId,
        string $clientSecret,
        string $orgId,
        string $username,
        string $password,
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
        if (! isset($parsedUrl['scheme'], $parsedUrl['host']) || ! $parsedUrl
        ) {
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
     * @throws ConnectionException|\JsonException
     */
    public function createKnownPlace(array $requestData): Response
    {
        return $this->callWfmApi(
            'POST',
            'api/v1/commons/known_places',
            $requestData,
        );
    }

    /**
     * Returns a list of paycodes available to a manager
     *
     * - When this operation is executed with no parameters, the system returns
     * a list of all available paycodes to the manager.
     * - When this operation includes an ID or qualifier, the system returns a
     * list containing the specified paycode, if available to the manager.
     *
     * @see https://developer.ukg.com/wfm/reference/retrieve-paycodes-as-manager
     *
     * @return Response the response from the Pro WFM API containing the JSON
     *                  data
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
     * @throws ConnectionException|\JsonException
     *
     * @see https://developer.ukg.com/wfm/reference/retrieve-all-persons
     */
    public function getAllPersonsPaginated(array $requestData = []): Response
    {
        return $this->callWfmApi(
            'POST',
            'api/v1/commons/persons/apply_read',
            $requestData,
        );
    }

    /**
     * Extract place IDs from a list of places
     */
    public function extractPlaceIds(array $places): array
    {
        return array_map(static function ($place) {
            return $place['id'];
        }, $places);
    }

    /**
     * Get all known places from the WFM using its API
     */
    public function getKnownPlaces(array $requestData = []): array
    {
        // TODO: Update how we are getting known places in the Controller so this can return just the $response
        //        return $this->callWfmApi('GET', 'api/v1/commons/known_places', $requestData);
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
     * @throws ConnectionException|\JsonException
     */
    public function getAdjustmentRules(array $requestData = []): Response
    {
        return $this->callWfmApi(
            'GET',
            'api/v1/timekeeping/setup/adjustment_rules',
            $requestData,
        );
    }

    /**
     * Retrieves the paginated list of Labor Category Entries using the
     * provided
     * Labor Category name from the WFM API
     *
     * @param  array  $requestData  request parameters
     * @return Response the response from the API
     *
     * @throws ConnectionException|\JsonException
     *
     * @see https://developer.ukg.com/wfm/reference/retrieve-paginated-list-of-labor-category-entries
     */
    public function getLaborCategoryEntriesPaginated(array $requestData = [],
    ): Response {
        return $this->callWfmApi(
            'POST',
            'api/v1/commons/labor_entries/apply_read',
            $requestData,
        );
    }

    /**
     * Clear the access token (used for logout)
     */
    public function clearAccessToken(): void
    {
        $this->accessToken = '';
    }

    /**
     * Retrieve labor category entries from WFM API
     *
     * @param  array  $requestData  Optional request parameters/filters
     * @return Response the response from the API
     *
     * @throws ConnectionException|\JsonException
     */
    public function getLaborCategoryEntries(array $requestData = []): Response
    {
        return $this->callWfmApi(
            'POST',
            'api/v1/commons/labor_entries/multi_read',
            $requestData,
        );
    }

    /**
     * Retrieve all labor categories from the WFM API.
     *
     * @param  array  $requestData  The data to be sent with the API request.
     * @return Response The response from the API.
     *
     * @throws \Illuminate\Http\Client\ConnectionException
     * @throws \JsonException
     */
    public function getLaborCategories(array $requestData = []): Response
    {
        return $this->callWfmApi(
            'GET',
            'api/v1/commons/labor_categories',
            $requestData,
        );
    }

    /**
     * @throws \Illuminate\Http\Client\ConnectionException
     * @throws \JsonException
     */
    public function getPercentAllocationRules(array $requestData = []): Response
    {
        // Add all_details flag to true to get more information
        return $this->callWfmApi(
            'GET',
            'api/v1/timekeeping/setup/percentage_allocation_rules?all_details=true',
        );
    }

    /**
     * Retrieve paginated data elements from the WFM API.
     *
     * @param  array  $requestData  Optional parameters for filtering or
     *                              pagination.
     * @return PromiseInterface|Response A promise resolving to an HTTP
     *                                   response object.
     *
     * @throws \Illuminate\Http\Client\ConnectionException
     * @throws \JsonException
     */
    public function getDataElementsPaginated(array $requestData = [],
    ): PromiseInterface|Response {
        return $this->callWfmApi(
            'GET',
            'api/v1/commons/data_dictionary/data_elements',
            $requestData,
        );
    }

    /**
     * Helper method to analyze data types in the request
     *
     * @throws \JsonException
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
                            $types[$key."[$index][$subKey]"] = gettype(
                                $subValue,
                            ).' ('.json_encode(
                                $subValue,
                                JSON_THROW_ON_ERROR,
                            ).')';
                        }
                    } else {
                        $types[$key."[$index]"] = gettype($item).' ('
                            .json_encode($item, JSON_THROW_ON_ERROR).')';
                    }
                }
            } else {
                $types[$key] = gettype($value).' ('.json_encode(
                    $value,
                    JSON_THROW_ON_ERROR,
                ).')';
            }
        }

        return $types;
    }
}
