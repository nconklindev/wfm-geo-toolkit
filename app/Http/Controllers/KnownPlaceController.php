<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKnownPlaceRequest;
use App\Http\Requests\UpdateKnownPlaceRequest;
use App\Models\KnownPlace;
use App\Services\KnownPlaceService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;
use Throwable;

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
            $knownPlace->deleteOrFail();

            // Authorize the action via the User Model
            // https://laravel.com/docs/12.x/authorization#via-the-user-model
            if ($request->user()->cannot('delete', $knownPlace)) {
                abort(403);
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
        $typesForUser = auth()->user()->types()->get();

        return view('known-places.edit', compact('knownPlace', 'typesForUser'));
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
     * @param  StoreKnownPlaceRequest  $request
     * @return RedirectResponse
     */
    public function store(StoreKnownPlaceRequest $request): RedirectResponse
    {
        $validated = $request->validated();


        // Create the Known Place first
        $knownPlace = auth()->user()->knownPlaces()->create($validated);

        // Only process locations if they were provided
        if (!empty($validated['savedLocations'])) {
            // Get the Business Structure Types for the user
            $types = auth()->user()->types()->orderBy('order')->get();

            // Transform the saved locations from the validated data
            // - Trim whitespace around each location
            // - Capitalize the first letter of each location
            $transformedLocations = array_map(function ($locationGroup) {
                // Apply transformations to each element in the inner array
                return array_map(function ($item) {
                    return is_string($item) ? trim(ucfirst($item)) : $item;
                }, $locationGroup);
            }, $validated['savedLocations']); // OK
//            dd($transformedLocations);

            // Process each location group (Acme/NC/1/..., Acme/NC/2/..., etc.)
            foreach ($transformedLocations as $locationGroup) {
                $parentId = null;
                $pathHierarchy = [];
                $currentPath = '';

                // Process each level in the hierarchy for this location group
                foreach ($types as $index => $type) {
                    if (isset($locationGroup[$index])) {
                        $nodeName = $locationGroup[$index];

                        // Build the path incrementally
                        $currentPath = $currentPath ? $currentPath.'/'.$nodeName : $nodeName;

                        // Add to path hierarchy
                        $pathHierarchy[] = [
                            'type' => $type->name,
                            'name' => $nodeName,
                            'level' => $type->order,
                        ];

                        if ($index === 0) {
                            // Check if the root node exists
                            $existingNode = auth()->user()->nodes()
                                ->where('name', $nodeName)
                                ->whereNull('parent_id')
                                ->where('business_structure_type_id', $type->id)
                                ->first();
                        } else {
                            // Check if child node exists
                            $existingNode = auth()->user()->nodes()
                                ->where('name', $nodeName)
                                ->where('parent_id', $parentId)
                                ->where('business_structure_type_id', $type->id)
                                ->first();
                        }

                        if ($existingNode) {
                            $node = $existingNode;
                        } else {
                            // Create the node
                            $node = auth()->user()->nodes()->create([
                                'business_structure_type_id' => $type->id,
                                'name' => $nodeName,
                                'path' => $currentPath,
                                'parent_id' => $parentId,
                                'path_hierarchy' => json_encode($pathHierarchy),
                            ]);
                        }

                        // Update parent_id for next iteration
                        $parentId = $node->id;

                        // Attach the full location to the Known Place
                        if ($index === count($types) - 1 || !isset($locationGroup[$index + 1])) {
                            $knownPlace->nodes()->attach($node->id, [
                                'path' => $currentPath,
                                'path_hierarchy' => json_encode($pathHierarchy),
                            ]);
                        }
                    }
                }
            }
        }

        // Get existing session places or initialize empty array
        $sessionPlaces = session('session_known_places', []);

        // Add the new ID to the array
        $sessionPlaces[] = $knownPlace->id;

        // Store back in session
        session(['session_known_places' => $sessionPlaces]);

        flash()
            ->option('position', 'bottom-right')
            ->option('timeout', 5000)
            ->success('Known place created successfully.');

        return redirect()->route('known-places.create');
    }

    /**
     * Show the form to create a new Known Place
     * @return Factory|View|Application|\Illuminate\View\View|object
     * @see KnownPlace
     */
    public function create()
    {
        $sessionPlaceIds = session('session_known_places', []);
        $typesForUser = auth()->user()->types()->get();

        // Start with an empty query result if there are no session places
        if (empty($sessionPlaceIds)) {
            // Create an empty paginator
            $sessionKnownPlaces = new Paginator([], 10);
        } else {
            // Only query if we have session places
            $sessionKnownPlaces = auth()->user()->knownPlaces()
                ->whereIn('id', $sessionPlaceIds)
                ->simplePaginate(10);
        }

        return view('known-places.create', compact('sessionKnownPlaces', 'typesForUser'));
    }

    /**
     * @param  UpdateKnownPlaceRequest  $request
     * @param  KnownPlace  $knownPlace
     * @return RedirectResponse
     */
    public function update(UpdateKnownPlaceRequest $request, KnownPlace $knownPlace): RedirectResponse
    {
        if ($request->user()->cannot('update', $knownPlace)) {
            abort(403);
        }

        $validated = $request->validated();

        $knownPlace->update($validated);

        flash()
            ->option('position', 'bottom-right')
            ->option('timeout', 5000)
            ->success('Known place updated successfully.');

        return redirect()->intended(route('known-places.index'));
    }
}
