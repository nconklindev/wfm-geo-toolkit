<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKnownPlaceRequest;
use App\Http\Requests\UpdateKnownPlaceRequest;
use App\Models\BusinessStructureNode;
use App\Models\KnownPlace;
use App\Models\User;
use App\Services\KnownPlaceService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;
use Throwable;

// Make sure this is imported

class KnownPlaceController extends Controller
{
    protected knownPlaceService $knownPlaceService;

    function __construct(KnownPlaceService $knownPlaceService)
    {
        $this->knownPlaceService = $knownPlaceService;
    }

    /**
     * @param  KnownPlace  $knownPlace
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
        Log::info("Deleting orphaned node {$node->id} with path {$node->path}");

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
     * @return Factory|View|Application|\Illuminate\View\View|object
     */
    public function edit(KnownPlace $knownPlace)
    {
        $leafNodes = $knownPlace->nodes()->whereIsLeaf()->get(['business_structure_nodes.path']);

        // Transform the collection of nodes into the array format expected by LocationInput
        // [['Seg1', 'Seg2'], ['Path2']]
        $assignedLocations = $leafNodes->map(function ($node) {
            // Split the path string into segments
            $segments = explode('/', $node->path ?? ''); // Use null coalesce for safety
            // Trim whitespace from each segment
            $segments = array_map('trim', $segments);
            // Filter out any potentially empty segments
            return array_values(array_filter($segments, fn($segment) => $segment !== ''));
        })->all(); // Convert the final Laravel Collection to a plain PHP array


        return view('known-places.edit', compact('knownPlace', 'assignedLocations'));
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

    /**
     * Store a newly created KnownPlace and associate location nodes.
     *
     * @param  StoreKnownPlaceRequest  $request
     * @return RedirectResponse
     */
    public function store(StoreKnownPlaceRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = auth()->user(); // Get the authenticated user

        // Create the Known Place first
        $knownPlace = $user->knownPlaces()->create($validated);

        // Process location paths using the helper method and attach
        $nodeIdsToAttach = [];
        if (!empty($validated['locations'])) {
            foreach ($validated['locations'] as $locationSegments) {
                $leafNode = $this->findOrCreateNodeByPathSegments($locationSegments, $user);
                if ($leafNode) {
                    $nodeIdsToAttach[$leafNode->id] = [
                        'user_id' => $user->id,
                        'path' => $leafNode->path
                    ]; // Prepare data for attach/sync
                }
            }
        }
        // Attach nodes with pivot data
        if (!empty($nodeIdsToAttach)) {
            // Use attach here as it's a new KnownPlace, no need to sync
            $knownPlace->nodes()->attach($nodeIdsToAttach);
        }

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
        $user = auth()->user();

        if (empty($sessionPlaceIds)) {
            $sessionKnownPlaces = new Paginator([], 10);
        } else {
            $paginator = $user->knownPlaces()
                ->whereIn('id', $sessionPlaceIds)
                ->orderByDesc('created_at')
                ->simplePaginate(10); // Fetch the paginator first

            // Use through() to modify each item in the paginator's collection
            $sessionKnownPlaces = $paginator->through(function ($knownPlace) {
                $maxLocationsToShow = 3;
                // Ensure locations is an array
                $locations = is_array($knownPlace->locations) ? $knownPlace->locations : [];
//                dd($locations);
                $locationCount = count($locations);

                // Add new properties to the object for the view to use
                $knownPlace->display_locations = array_slice($locations, 0, $maxLocationsToShow);
                $knownPlace->remaining_locations_count = max(0, $locationCount - $maxLocationsToShow);

                // Return the modified object (it's modified in place, but returning is good practice)
                return $knownPlace;
            });
        }

        return view('known-places.create', compact('sessionKnownPlaces'));
    }

    /**
     * Finds or creates the necessary BusinessStructureNode hierarchy for a given path.
     * Returns the final leaf node.
     *
     * @param  array<int, string>  $locationSegments  Array of path segments (e.g., ['Acme', 'NC', 'Store 01'])
     * @param  User  $user  The user owning the nodes.
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
                    // Add any other default fields for BusinessStructureNode if needed
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
     * Update the specified KnownPlace and sync location nodes.
     *
     * @param  UpdateKnownPlaceRequest  $request
     * @param  KnownPlace  $knownPlace
     * @return RedirectResponse
     */
    public function update(UpdateKnownPlaceRequest $request, KnownPlace $knownPlace): RedirectResponse
    {
        // Authorization is handled by UpdateKnownPlaceRequest `authorize` method

        $validated = $request->validated();
        $user = $request->user();

        // Update the main KnownPlace attributes (excluding locations for now)
        $knownPlace->update($request->except('locations'));

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

        // Redirect back to index or wherever appropriate after update
        return redirect()->intended(route('known-places.index'));
    }

}
