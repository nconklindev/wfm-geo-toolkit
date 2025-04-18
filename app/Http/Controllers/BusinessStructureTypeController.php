<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReassignAndDeleteTypeRequest;
use App\Http\Requests\StoreBusinessStructureTypeRequest;
use App\Http\Requests\UpdateBusinessStructureTypeRequest;
use App\Models\BusinessStructureNode;
use App\Models\BusinessStructureType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

// Keep if using reassignment logic

// Use Gate for authorization

class BusinessStructureTypeController extends Controller
{
    /**
     * Display a listing of the resource for the authenticated user.
     */
    public function index(): View
    {
        // Fetch types belonging to the user, ordered by their 'order' preference
        $types = auth()->user()->types()->get(); // Use the new relationship name

        return view('business-structure.types.index', compact('types'));
    }

    /**
     * Store a newly created resource in storage for the authenticated user.
     */
    public function store(StoreBusinessStructureTypeRequest $request): RedirectResponse
    {
        // Authorization is handled by the Form Request's authorize method
        // or could be checked here with Gate::authorize(...)

        $validated = $request->validated();
        $user = Auth::user();

        // Validation for uniqueness (name per user) should be handled in StoreBusinessStructureTypeRequest

        // Create the type directly associated with the user
        $type = $user->types()->create($validated);

        flash()
            ->option('position', 'bottom-right')
            ->option('timeout', 5000)
            ->success('Created new Business Structure Type: '.$type->name);

        return redirect()->route('business-structure.types.index');
    }

    /**
     * Show the form for creating a new resource for the authenticated user.
     */
    public function create(): View
    {
        // Authorization: Ensure user can create types (optional, if policy exists)
        Gate::authorize('create', BusinessStructureType::class);

        return view('business-structure.types.create');
    }

    /**
     * Show the form for editing the specified resource owned by the authenticated user.
     */
    public function edit(BusinessStructureType $type): View
    {
        // Authorization: Ensure the user owns this specific type
        Gate::authorize('update', $type); // Assumes a policy exists: $user->id === $type->user_id

        // No need for pivot data anymore, just pass the type
        return view('business-structure.types.edit', compact('type'));
    }

    /**
     * Check usage and initiate deletion or prompt for reassignment for the specified resource.
     */
    public function destroy(Request $request, BusinessStructureType $type): RedirectResponse
    {
        // Authorization: Ensure the user owns this specific type
        Gate::authorize('delete', $type);

        $user = Auth::user(); // Get the authenticated user

        // --- Check if the type is used by THIS user's nodes ---
        // IMPORTANT: Scope the check to the current user's nodes
        $usageCount = $type->businessStructureNodes() // Get related nodes
        ->where('user_id', $user->id)   // Filter by the current user
        ->count();

        if ($usageCount > 0) {
            // --- Type is in use - Redirect to confirmation step ---
            Log::info("User {$user->id} attempting to delete their type {$type->id} ('{$type->name}') which is in use by {$usageCount} of their nodes. Redirecting to confirm delete.");
            // Flash data or simply rely on route model binding in the next step
            return redirect()->route('business-structure.types.confirm-delete', ['type' => $type->id]);
        } else {
            // --- Type is NOT in use by this user's nodes - Delete directly ---
            Log::info("User {$user->id} deleting their unused type {$type->id} ('{$type->name}').");
            $typeName = $type->name; // Store name before deleting
            $type->delete();

            flash()
                ->option('position', 'bottom-right')
                ->option('timeout', 5000)
                ->success('Deleted Business Structure Type: '.$typeName);

            return redirect()->route('business-structure.types.index');
        }
    }

    /**
     * Display the confirmation form for deleting a type that's in use by the user.
     */
    public function confirmDelete(Request $request, BusinessStructureType $type): View|RedirectResponse
    {
        // Authorization: Ensure the user owns this specific type
        Gate::authorize('delete', $type);

        $user = Auth::user();

        // --- Get the specific nodes using this type for the current user ---
        $nodesUsingType = $type->businessStructureNodes()
            ->where('user_id', $user->id)
            ->orderBy('name')
            ->get(); // Fetch the collection

        // Re-verify usage count, scoped to the user
        $usageCount = $type->businessStructureNodes()
            ->where('user_id', $user->id)
            ->count();

        if ($usageCount === 0) {
            // If usage disappeared, redirect back with info (or just delete it)
            flash()->option('position', 'bottom-right')->option('timeout', 5000)
                ->info('Type '.$type->name.' is no longer in use and can now be deleted.');
            // Optionally delete here: $type->delete();
            return redirect()->route('business-structure.types.index');
        }

        // Get other types owned by this user for the dropdown
        $replacementTypes = $user->types()
            ->where('id', '!=', $type->id) // Exclude the type being deleted
            ->orderBy('order')
            ->get();

        // Check if there are any other types to reassign to
        if ($replacementTypes->isEmpty()) {
            return redirect()->route('business-structure.types.index')->with('customError',
                'Cannot delete Type "'.$type->name.'" because it\'s in use, and you have no other Types defined to reassign the locations to. Please create another Type first.');
        }

        Log::info("Showing confirmation prompt to user {$user->id} for deleting their type {$type->id} ('{$type->name}'). Usage count: {$usageCount}.");

        return view('business-structure.types.confirm-delete',
            compact('type', 'usageCount', 'replacementTypes', 'nodesUsingType'));
    }

    /**
     * Process the reassignment of user's nodes and delete the original type.
     */
    public function reassignAndDelete(
        ReassignAndDeleteTypeRequest $request,
        BusinessStructureType $type
    ): RedirectResponse {
        // Authorization: Ensure the user owns this specific type
        Gate::authorize('delete', $type); // Also implicitly checked by Form Request authorize potentially

        $user = Auth::user();
        $validated = $request->validated(); // Get validated replacement_type_id
        $replacementTypeId = $validated['replacement_type_id'];
        $originalTypeName = $type->name; // Store name before deleting

        Log::info("User {$user->id} confirmed deletion for their type {$type->id} ('{$originalTypeName}'). Reassigning their nodes to type {$replacementTypeId}.");

        // --- Perform the mass update - ONLY for the current user's nodes ---
        $updatedCount = BusinessStructureNode::where('business_structure_type_id', $type->id)
            ->where('user_id', $user->id) // Crucial scope!
            ->update(['business_structure_type_id' => $replacementTypeId]);

        Log::info("Updated {$updatedCount} BusinessStructureNode records for user {$user->id} from type {$type->id} to {$replacementTypeId}.");

        // --- Delete the original type ---
        $type->delete();

        flash()->option('position', 'bottom-right')->option('timeout', 5000)
            ->success('Locations reassigned successfully. Deleted Type: '.$originalTypeName);

        return redirect()->route('business-structure.types.index');
    }

    /**
     * Update the specified resource in storage owned by the authenticated user.
     */
    public function update(UpdateBusinessStructureTypeRequest $request, BusinessStructureType $type): RedirectResponse
    {
        // Authorization handled in UpdateBusinessStructureTypeRequest

        // Check if the 'order' is being changed and if the type is in use
        if (isset($validatedData['order']) && $validatedData['order'] != $type->order) {
            if ($type->businessStructureNodes()->exists()) {
                return redirect()->back()
                    ->withErrors(['order' => __('Cannot change the order of a type that is currently assigned to one or more locations.')])
                    ->withInput(); // Keep the user's input
            }
        }


        // Get validated data
        $validated = $request->validated();

        // Update the type directly
        $type->update($validated);

        flash()
            ->option('position', 'bottom-right')
            ->option('timeout', 5000)
            ->success('Updated Business Structure Type: '.$type->name);

        return redirect()->route('business-structure.types.index');
    }
}
