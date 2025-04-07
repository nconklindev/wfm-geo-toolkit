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
        return view('known-places.edit', compact('knownPlace'));
    }

    public function index()
    {
        $knownPlaces = auth()->user()->knownPlaces()->paginate(10);
        return view('known-places.index', compact('knownPlaces'));
    }

    public function show(KnownPlace $knownPlace)
    {
        return view('known-places.show', compact('knownPlace'));
    }

    /**
     * @param  StoreKnownPlaceRequest  $request
     * @return RedirectResponse
     */
    public function store(StoreKnownPlaceRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Create through the relationship
        $knownPlace = auth()->user()->knownPlaces()->create($validated);

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

        return redirect()->route('known-places.create', compact('sessionPlaces'));
    }

    /**
     * Show the form to create a new Known Place
     * @return Factory|View|Application|\Illuminate\View\View|object
     * @see KnownPlace
     */
    public function create()
    {
        $sessionPlaceIds = session('session_known_places', []);

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

        return view('known-places.create', compact('sessionKnownPlaces'));
    }

    /**
     * @param  UpdateKnownPlaceRequest  $request
     * @param  KnownPlace  $knownPlace
     * @return RedirectResponse
     */
    public function update(UpdateKnownPlaceRequest $request, KnownPlace $knownPlace): RedirectResponse
    {
        $validated = $request->validated();

        $knownPlace->update($validated);

        flash()
            ->option('position', 'bottom-right')
            ->option('timeout', 5000)
            ->success('Known place updated successfully.');

        return redirect()->intended(route('known-places.index'));
    }
}
