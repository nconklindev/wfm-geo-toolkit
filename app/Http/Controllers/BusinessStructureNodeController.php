<?php

namespace App\Http\Controllers;

use App\Models\BusinessStructureNode;
use Illuminate\Http\Request;

// Add this if not already present

// ... other use statements

class BusinessStructureNodeController extends Controller
{
    public function index(Request $request)
    {

        $user = auth()->user();
        // Get the known places with their associated leaf nodes only
        // Get leaf nodes with count of associated known places
        $leafNodes = $user->nodes()->whereIsLeaf()->withCount('knownPlaces')->get();

        // Get the entire tree with a single query, ordered hierarchically
        $nodes = auth()->user()->nodes()
            ->with([
                'user' => function ($query) {
                    $query->where('users.id', auth()->id());
                }
            ])
            ->withDepth()
            ->defaultOrder()
            ->get()
            ->toTree();

        // Create a lookup to identify which nodes have descendants with known places
        $nodesWithAssignedDescendants = [];

        // First, identify leaf nodes with known places
        $leafNodesWithKnownPlaces = $leafNodes->filter(function ($node) {
            return ($node->known_places_count ?? 0) > 0;
        })->pluck('id');

        // Then, for each leaf node with known places, mark all its ancestors
        foreach ($leafNodesWithKnownPlaces as $leafId) {
            $leafNode = $leafNodes->firstWhere('id', $leafId);
            $ancestorIds = BusinessStructureNode::ancestorsOf($leafId)->pluck('id');

            foreach ($ancestorIds as $ancestorId) {
                $nodesWithAssignedDescendants[$ancestorId] = true;
            }
        }


        return view('locations.index', [
            'nodes' => $nodes,
            'leafNodes' => $leafNodes,
            'nodesWithAssignedDescendants' => $nodesWithAssignedDescendants,
        ]);
    }

    // ... other methods

    public function show(BusinessStructureNode $node)
    {
        // Define how many items per page (you can make this configurable)
        $perPage = 15;

        // Query the relationship and paginate the results
        $knownPlaces = $node->knownPlaces()
            ->orderBy('name') // Optional: Order the results
            ->paginate($perPage);

        // Pass the node and the paginated known places to the view
        return view('locations.show', compact('node', 'knownPlaces'));
    }
}
