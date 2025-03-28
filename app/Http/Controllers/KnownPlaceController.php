<?php

namespace App\Http\Controllers;

use App\Models\KnownPlace;
use App\Services\KnownPlaceService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Validation\Rule;

class KnownPlaceController extends Controller
{
    protected knownPlaceService $knownPlaceService;

    function __construct(KnownPlaceService $knownPlaceService)
    {
        $this->knownPlaceService = $knownPlaceService;
    }

    public function index()
    {
        $knownPlaces = auth()->user()->knownPlaces()->paginate(10);
        return view('known-places.index', compact('knownPlaces'));
    }

    /**
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('known_places')->where('user_id', auth()->id())
            ],
            'description' => 'nullable|string|max:255',
            'latitude' => 'required|decimal:2,10|max:90',
            'longitude' => 'required|decimal:2,10|max:180',
            'radius' => 'required|integer',
            'accuracy' => 'required|integer|max:5000',
            // TODO: Add validation against added/imported Locations?
            'locations' => ['nullable', 'string'],
            'locations.*' => ['regex:/^[A-Za-z0-9 ]+(?:\/[A-Za-z0-9 ]+)*$/'],
            'validation_order' => 'required|array',
            'validation_order.*' => [
                Rule::in(['gps', 'wifi']),
            ],
        ]);

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
     * @param  KnownPlace  $knownPlace
     * @return RedirectResponse
     */
    public function destroy(KnownPlace $knownPlace)
    {
        // Check if the current user owns this place
        abort_if(auth()->user()->id !== $knownPlace->user_id, 403);

        $knownPlace->delete();

        flash()
            ->option('position', 'bottom-right')
            ->option('timeout', 5000)
            ->success('Known place deleted successfully.');

        return redirect()->back(fallback: route('known-places.index'));
    }

    /**
     * @param  KnownPlace  $knownPlace
     * @return Factory|View|Application|\Illuminate\View\View|object
     */
    public function edit(KnownPlace $knownPlace)
    {
        return view('known-places.edit', compact('knownPlace'));
    }

    /**
     * @param  Request  $request
     * @param  KnownPlace  $knownPlace
     * @return RedirectResponse
     */
    public function update(Request $request, KnownPlace $knownPlace): RedirectResponse
    {
        // Authorization
        if ($knownPlace->user_id !== auth()->id()) {
            flash()
                ->option('position', 'bottom-right')
                ->option('timeout', 5000)
                ->error('You do not have permission to update this known place.');

            return redirect()->intended(route('home'));
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('known_places')
                    ->where('user_id', auth()->id())
                    ->ignore($knownPlace->id),
            ],
            'description' => 'nullable|string|max:255',
            'latitude' => 'required|decimal:2,10|max:90|min:-90',
            'longitude' => 'required|decimal:2,10|max:180|min:-180',
            'radius' => 'required|integer|max:2147483647',
            'accuracy' => 'required|integer|max:5000',
            // TODO: Add validation against added/imported Locations?
            'locations' => ['nullable', 'string'],
            'locations.*' => ['regex:/^[A-Za-z0-9 ]+(?:\/[A-Za-z0-9 ]+)*$/'],
            'validation_order' => 'required|array',
            'validation_order.*' => [
                Rule::in(['gps', 'wifi']),
            ],
        ]);

        $knownPlace->update($validated);

        flash()
            ->option('position', 'bottom-right')
            ->option('timeout', 5000)
            ->success('Known place updated successfully.');

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
}
