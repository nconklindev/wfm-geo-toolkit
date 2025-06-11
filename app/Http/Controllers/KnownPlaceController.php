<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKnownPlaceRequest;
use App\Http\Requests\StoreWfmKnownPlaceRequest;
use App\Http\Requests\UpdateKnownPlaceRequest;
use App\Models\BusinessStructureNode;
use App\Models\KnownPlace;
use App\Models\User;
use App\Services\KnownPlaceService;
use App\Services\WfmService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class KnownPlaceController extends Controller
{
    protected KnownPlaceService $knownPlaceService;
    protected WfmService $wfmService;

    function __construct(KnownPlaceService $knownPlaceService, WfmService $wfmService)
    {
        $this->knownPlaceService = $knownPlaceService;
        $this->wfmService = $wfmService;
    }

    /**
     * @param  Request  $request
     * @param  KnownPlace  $knownPlace
     *
     * @return RedirectResponse
     */
    public function destroy(Request $request, KnownPlace $knownPlace): RedirectResponse
    {
        try {
            // Authorize the action via the User Model
            // https://laravel.com/docs/12.x/authorization#via-the-user-model
            if ($request->user()->cannot('delete', $knownPlace)) {
                abort(403);
            }

            // Get the nodes attached to this place before deletion
            $nodeIds = $knownPlace->nodes()->pluck('business_structure_nodes.id')->toArray();

            $knownPlace->deleteOrFail();

            // Check each node to see if it's orphaned (not attached to any other places)
            foreach ($nodeIds as $nodeId) {
                $node = BusinessStructureNode::find($nodeId);
                if ($node) {
                    $this->deleteNodeIfOrphaned($node);
                }

            }


        } catch (Throwable $e) {
            Log::error($e); // Log the error

            // Flash the error letting the user know something happened
            flash()
                ->use('theme.minimal')
                ->option('position', 'bottom-right')
                ->option('timeout', 5000)
                ->error("$knownPlace->name could not be deleted. Please try again.");

            // Redirect back with errors
            return back()->withErrors(['message' => 'Unable to delete known place.']);
        }

        // Flash the success
        flash()
            ->use('theme.minimal')
            ->option('position', 'bottom-right')
            ->option('timeout', 5000)
            ->success("$knownPlace->name deleted successfully.");

        // Redirect intended route
        // This will usually be the index route
        return redirect()->intended(route('known-places.index'));
    }

    /**
     * Recursively delete a node and its parents if they're orphaned
     *
     * @param  BusinessStructureNode  $node  The node to check and possibly delete
     *
     * @return void
     */
    private function deleteNodeIfOrphaned(BusinessStructureNode $node): void
    {
        // Skip if the node is still attached to any known places
        if ($node->knownPlaces()->count() > 0) {
            return;
        }

        // Skip if the node still has any children
        if ($node->children()->count() > 0) {
            return;
        }

        // At this point, the node is orphaned with no children and no known places
        $parentId = $node->parent_id;

        // Log the node we're about to delete
        Log::info("Deleting orphaned node $node->id with path $node->path");

        // Delete the orphaned node
        $node->delete();

        // If this node had a parent, check if the parent is now orphaned too
        if ($parentId) {
            $parent = BusinessStructureNode::find($parentId);
            if ($parent) {
                // Recursively check the parent
                $this->deleteNodeIfOrphaned($parent);
            }
        }
    }

    public function downloadSample()
    {
        $path = storage_path('app/public/samples/test.json');
        // TODO: Need to get a proper sample from a CFN or something
        return response()->download($path, 'sample_known_places_response.json', [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @param  KnownPlace  $knownPlace
     *
     * @return Factory|View|Application|\Illuminate\View\View|object
     */
    public function edit(KnownPlace $knownPlace)
    {
        $knownPlace->load('user.groups');
        $leafNodes = $knownPlace->nodes()->whereIsLeaf()->get(['business_structure_nodes.path']);

        // Transform the collection of nodes into the array format expected by LocationInput
        $assignedLocations = $leafNodes->map(function ($node) {
            $segments = explode('/', $node->path ?? '');
            $segments = array_map('trim', $segments);
            return array_values(array_filter($segments, fn($segment) => $segment !== ''));
        })->all();

        // Get the groups for the dropdown
        $groups = $knownPlace->user->groups;

        return view('known-places.edit', compact('knownPlace', 'assignedLocations', 'groups'));
    }

    public function index()
    {
        $knownPlaces = auth()->user()->knownPlaces()->paginate(10);
        return view('known-places.index', compact('knownPlaces'));
    }

    public function show(KnownPlace $knownPlace)
    {
        $knownPlace->load([
            'nodes' => function ($query) {
                $query->select([
                    'business_structure_nodes.id',
                    'business_structure_nodes.path',
                    'business_structure_nodes._lft',
                    'business_structure_nodes._rgt',
                    'business_structure_nodes.parent_id'
                ])->orderBy('_lft',
                    'desc');
            }
        ]);

        return view('known-places.show', compact('knownPlace'));
    }

    public function store(StoreKnownPlaceRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = auth()->user();

        $knownPlace = DB::transaction(function () use ($request, $validated, $user) {
            // Create the Known Place - this will trigger the observer
            $knownPlace = $user->knownPlaces()->create($validated);

            // Set group relationship using many-to-many if provided and valid
            if ($request->has('group_id') && !empty($request->group_id)) {
                // Verify the group belongs to the user (security check)
                $group = $user->groups()->find($request->group_id);
                if ($group) {
                    $knownPlace->groups()->attach($group->id);
                }
            }

            // Process location paths using the helper method and attach
            $nodeIdsToAttach = [];
            if (!empty($validated['locations'])) {
                foreach ($validated['locations'] as $locationSegments) {
                    $leafNode = $this->findOrCreateNodeByPathSegments($locationSegments, $user);
                    if ($leafNode) {
                        $nodeIdsToAttach[$leafNode->id] = [
                            'user_id' => $user->id,
                            'path' => $leafNode->path
                        ];
                    }
                }
            }

            // Attach nodes with pivot data
            if (!empty($nodeIdsToAttach)) {
                $knownPlace->nodes()->attach($nodeIdsToAttach);
                Log::info("KnownPlaceController: Attached ".count($nodeIdsToAttach)." nodes to KnownPlace {$knownPlace->id}");
            }

            return $knownPlace;
        });

        // Add the new Known Place ID to the session list for the create page
        $sessionPlaces = session('session_known_places', []);
        if (!in_array($knownPlace->id, $sessionPlaces)) {
            $sessionPlaces[] = $knownPlace->id;
        }
        session(['session_known_places' => $sessionPlaces]);

        flash()
            ->option('position', 'bottom-right')
            ->option('timeout', 5000)
            ->success('Known place created successfully.');

        return redirect()->route('known-places.create');
    }

    public function create()
    {
        $sessionPlaceIds = session('session_known_places', []);
        $user = auth()->user()->load('groups');

        // Pass the actual groups collection, not an array with indices
        $groups = $user->groups;

        if (empty($sessionPlaceIds)) {
            $sessionKnownPlaces = new Paginator([], 10);
        } else {
            $paginator = $user->knownPlaces()
                ->whereIn('id', $sessionPlaceIds)
                ->orderByDesc('created_at')
                ->simplePaginate(10);

            $sessionKnownPlaces = $paginator->through(function ($knownPlace) {
                $maxLocationsToShow = 3;
                $locations = is_array($knownPlace->locations) ? $knownPlace->locations : [];
                $locationCount = count($locations);

                $knownPlace->display_locations = array_slice($locations, 0, $maxLocationsToShow);
                $knownPlace->remaining_locations_count = max(0, $locationCount - $maxLocationsToShow);

                return $knownPlace;
            });
        }

        return view('known-places.create', compact('sessionKnownPlaces', 'groups'));
    }

    /**
     * Finds or creates the necessary BusinessStructureNode hierarchy for a given path.
     * Returns the final leaf node.
     *
     * @param  array<int, string>  $locationSegments  Array of path segments (e.g., ['Acme', 'NC', 'Store 01'])
     * @param  User  $user  The user owning the nodes.
     *
     * @return BusinessStructureNode|null The leaf node, or null if segments are empty.
     */
    private function findOrCreateNodeByPathSegments(array $locationSegments, User $user): ?BusinessStructureNode
    {
        $parentId = null;
        $currentPath = '';
        $lastNode = null;

        foreach ($locationSegments as $segmentName) {
            // Prepare the node name
            $nodeName = trim(ucfirst($segmentName));

            // Skip empty segments
            if (empty($nodeName)) {
                continue;
            }

            // Build the path string incrementally
            $currentPath = $currentPath ? $currentPath.'/'.$nodeName : $nodeName;

            // Find existing or create a new node for the current segment
            // Uniqueness is based on user, name, and parent_id
            $node = $user->nodes()->updateOrCreate(
                [
                    'name' => $nodeName,
                    'parent_id' => $parentId,
                ],
                [
                    'path' => $currentPath, // Ensure path is set on create/update
                ]
            );

            // Ensure the path is correctly set even if the node already existed
            // This handles cases where the path might not have been stored previously
            if ($node->path !== $currentPath) {
                $node->path = $currentPath;
                $node->save();
            }

            $parentId = $node->id;
            $lastNode = $node; // Track the most recently processed node
        }

        // Return the last node processed (the leaf node for this path)
        return $lastNode;
    }

    /**
     * Store a newly created WFM Known Place.
     *
     * @param  StoreWfmKnownPlaceRequest  $request
     *
     * @return RedirectResponse
     */
    public function storeWfm(StoreWfmKnownPlaceRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $credentials = [
            'client_id' => $validated['client_id'],
            'client_secret' => $validated['client_secret'],
            'org_id' => $validated['org_id'],
            'username' => $validated['username'],
            'hostname' => $validated['hostname'],
            // Intentionally exclude password for security
        ];

        session(['wfm_credentials' => $credentials]);

        try {
            // Set the hostname first so the service can determine the correct token URL
            $this->wfmService->setHostname($validated['hostname']);

            // Authenticate with WFM
            $authenticated = $this->wfmService->authenticate(
                $validated['client_id'],
                $validated['client_secret'],
                $validated['org_id'],
                $validated['username'],
                $validated['password']
            );

            if (!$authenticated) {
                flash()
                    ->use('theme.minimal')
                    ->option('position', 'bottom-right')
                    ->option('timeout', 5000)
                    ->error('Authentication failed. Please check your credentials.');

                return redirect()->back()->withInput();
            }

            // Get existing places to determine next ID
            $wfmPlaces = $this->wfmService->getKnownPlaces();
            if (empty($wfmPlaces)) {
                flash()
                    ->use('theme.minimal')
                    ->option('position', 'bottom-right')
                    ->option('timeout', 5000)
                    ->error('Failed to retrieve existing known places from WFM.');

                return redirect()->back()->withInput();
            }

            $placeIds = $this->wfmService->extractPlaceIds($wfmPlaces);
            $nextId = $this->wfmService->getNextAvailableId($placeIds);

            // Prepare payload for creating a new place
            $payload = [
                'id' => $nextId,
                'name' => $validated['name'],
                'description' => $validated['description'] ?? '',
                'radius' => (int) $validated['radius'],
                'accuracy' => (int) $validated['accuracy'],
                'latitude' => (float) $validated['latitude'],
                'longitude' => (float) $validated['longitude'],
                'active' => true
            ];

            // Create the place in WFM
            $response = $this->wfmService->createKnownPlace($payload);

            if (!$response->successful()) {
                $error = $this->wfmService->handleWfmError($response, 'Creating known place');

                flash()
                    ->use('theme.minimal')
                    ->option('position', 'bottom-right')
                    ->option('timeout', 5000)
                    ->error($error['message']);

                return redirect()->back()->withInput();
            }

            flash()
                ->use('theme.minimal')
                ->option('position', 'bottom-right')
                ->option('timeout', 5000)
                ->success('Known place created successfully in WFM.');

            // On success, redirect with saved credentials in flash data
            return redirect()->route('known-places.wfm-import')
                ->with('wfm_credentials', $credentials);
        } catch (Throwable $e) {
            Log::error('Unexpected error in WFM integration', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            flash()
                ->use('theme.minimal')
                ->option('position', 'bottom-right')
                ->option('timeout', 5000)
                ->error('An unexpected error occurred. Please try again.');

            return redirect()->back()->withInput();
        }
    }

    public function update(UpdateKnownPlaceRequest $request, KnownPlace $knownPlace): RedirectResponse
    {
        // Authorization is handled by UpdateKnownPlaceRequest `authorize` method
        $validated = $request->validated();
        $user = $request->user();

        // Update the main KnownPlace attributes (excluding locations for now)
        $knownPlace->update($request->except('locations'));

        // Handle group relationship using many-to-many
        if ($request->has('group_id')) {
            if (empty($request->group_id)) {
                // Remove all group assignments
                $knownPlace->groups()->detach();
            } else {
                // Verify the group belongs to the user (security check)
                $group = $user->groups()->find($request->group_id);
                if ($group) {
                    // Replace current group assignment with the new one
                    $knownPlace->groups()->sync([$group->id]);
                }
            }
        }

        // --- Location Node Synchronization ---
        $nodeIdsToSync = [];
        // Process submitted locations only if the key exists and is an array
        if (isset($validated['locations']) && is_array($validated['locations'])) {
            foreach ($validated['locations'] as $locationSegments) {
                // Use the helper function to find or create the leaf node
                $leafNode = $this->findOrCreateNodeByPathSegments($locationSegments, $user);
                if ($leafNode) {
                    // Store the leaf node ID and pivot data for syncing
                    $nodeIdsToSync[$leafNode->id] = ['user_id' => $user->id, 'path' => $leafNode->path];
                }
            }
        }

        // Sync the relationship:
        // - Attaches nodes in $nodeIdsToSync that aren't already attached.
        // - Detaches nodes that are attached but not in $nodeIdsToSync.
        // - Updates pivot data for nodes that remain attached.
        $knownPlace->nodes()->sync($nodeIdsToSync);
        // --- End Location Node Synchronization ---

        // --- Update the 'locations' array column for search ---
        // Reload the relationship to get the final list of attached nodes after sync
        $knownPlace->load('nodes:id,path'); // Eager load only necessary columns

        // Get the 'path' attribute from each attached node
        $currentLocationPaths = $knownPlace->nodes->pluck('path')->toArray();

        // Update the 'locations' column on the KnownPlace model
        $knownPlace->locations = $currentLocationPaths;
        $knownPlace->saveQuietly(); // Use saveQuietly to prevent dispatching updated event again if not needed
        // --- End Update 'locations' array column ---

        flash()
            ->option('position', 'bottom-right')
            ->option('timeout', 5000)
            ->success('Known place updated successfully.');

        // Redirect back to index after update
        return redirect()->intended(route('known-places.index'));
    }

    public function wfmImport()
    {
        return view('known-places.wfm-import');
    }
}
