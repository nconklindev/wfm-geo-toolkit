<?php

namespace App\Http\Controllers;

use App\Models\KnownPlace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KnownPlaceController extends Controller
{
    public function index()
    {
        //
    }

    public function create()
    {
        $knownPlaces = auth()->user()->knownPlaces()->paginate(10);
        return view('known-places.create', compact('knownPlaces'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            Rule::unique('known_places')->where('user_id', auth()->id()),
            'description' => 'nullable|string|max:255',
            'latitude' => 'required|decimal:2,10',
            'longitude' => 'required|decimal:2,10',
            'radius' => 'required|integer',
            'gps_accuracy_threshold' => 'required|integer',
            // TODO: Add validation against added/imported Locations?
            'location_path' => ['nullable', 'regex:/^[A-Za-z0-9 ]+(?:\/[A-Za-z0-9 ]+)*$/'],
            'validation_order' => 'required|array',
            'validation_order.*' => [
                Rule::in(['gps', 'wifi']),
            ],
        ]);

        // Follow WFM Known Place rules
        // Check if the user has a Known Place with that name already
        // Disallow non-unique names
        // TODO: The validation rule may take care of this
//        $existingPlace = auth()->user()->knownPlaces()
//            ->where('name', $validated['name'])
//            ->first();

//        if ($existingPlace) {
//            flash()
//                ->option('position', 'bottom-right')
//                ->option('timeout', 5000)
//                ->error('A known place with this name already exists.');
//        }


        // Create through the relationship
        auth()->user()->knownPlaces()->create($validated);

        flash()
            ->option('position', 'bottom-right')
            ->option('timeout', 5000)
            ->success('Known place created successfully.');

        return back();
    }

    public function destroy(KnownPlace $knownPlace)
    {
        // Check if the current user owns this place
        abort_if(auth()->user()->id !== $knownPlace->user_id, 403);

        $knownPlace->delete();

        flash()
            ->option('position', 'bottom-right')
            ->option('timeout', 5000)
            ->success('Known place deleted successfully.');

        return redirect()->route('known-places.create');
    }

    public function edit(KnownPlace $knownPlace)
    {
        return view('known-places.edit', compact('knownPlace'));
    }

    public function update(Request $request, KnownPlace $knownPlace): RedirectResponse
    {
        // Authorization
        if ($knownPlace->user_id !== auth()->id()) {
            flash()
                ->option('position', 'bottom-right')
                ->option('timeout', 5000)
                ->error('You do not have permission to update this known place.');

            return redirect()->route('known-places.index');
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
            'latitude' => 'required|decimal:2,10',
            'longitude' => 'required|decimal:2,10',
            'radius' => 'required|integer',
            'gps_accuracy_threshold' => 'required|integer',
            'location_path' => ['nullable', 'regex:/^[A-Za-z0-9 ]+(?:\/[A-Za-z0-9 ]+)*$/'],
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

        return redirect()->route('known-places.create');

    }
}
