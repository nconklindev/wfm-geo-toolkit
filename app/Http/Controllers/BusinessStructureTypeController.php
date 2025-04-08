<?php

namespace App\Http\Controllers;

use App\Models\BusinessStructureType;
use Illuminate\Http\Request;

class BusinessStructureTypeController extends Controller
{
    public function index()
    {
        return view('business-structure.types.index');
    }

    public function store(Request $request)
    {
        // Validate the inputs
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'hierarchy_order' => 'required|integer|min:1|max:9999',
            'color' => 'nullable|string|max:50'
        ]);

        $user = auth()->user();

        // Check if the name of the type AND order already exist in the main table
        $exists = BusinessStructureType::where('name', $validated['name'])
            ->where('hierarchy_order', $validated['hierarchy_order'])
            ->first();

        // If it exists, check if the user is already attached to it
        if ($exists) {
            // Check if the user is already associated with this type
            $alreadyAttached = $user->types()
                ->where('business_structure_type_id', $exists->id)
                ->exists();

            if (!$alreadyAttached) {
                // User is not yet attached to this type, so attach them
                $user->types()->attach($exists->id, [
                    'description' => $validated['description'],
                    'hex_color' => $validated['color']
                ]);

                flash()
                    ->option('position', 'bottom-right')
                    ->option('timeout', 5000)
                    ->success('Associated with existing Business Structure Type.');

                return redirect()->route('business-structure.types.index');
            } else {
                flash()
                    ->option('position', 'bottom-right')
                    ->option('timeout', 5000)
                    ->info('This type already exists');

                return redirect()->route('business-structure.types.index');
            }
        } else {
            // Create the new type
            $type = BusinessStructureType::create($validated);

            // Attach the user to the new type
            $user->types()->attach($type->id, [
                'description' => $validated['description'] ?? null,
                'hex_color' => $validated['color'] ?? null
            ]);

            return redirect()->route('business-structure.types.index')
                ->with('success', 'New Business Structure Type created successfully.');
        }
    }

    public function create()
    {
        return view('business-structure.types.create');
    }
}
