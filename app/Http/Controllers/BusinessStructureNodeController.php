<?php

namespace App\Http\Controllers;

use App\Models\BusinessStructureNode;
use Illuminate\Http\Request;

class BusinessStructureNodeController extends Controller
{
    public function index(Request $request)
    {

        $user = auth()->user();
        $types = $user->types;
        // Get the known places with their associated leaf nodes only
        // Get leaf nodes with count of associated known places
        $leafNodes = $user->nodes()->whereIsLeaf()->withCount('knownPlaces')->get();

        // Get the entire tree with a single query, ordered hierarchically
        $nodes = BusinessStructureNode::with('type')->withDepth()->defaultOrder()->get()->toTree();

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


        return view('business-structure.locations.index', [
            'nodes' => $nodes,
            'types' => $types,
            'leafNodes' => $leafNodes,
            'nodesWithAssignedDescendants' => $nodesWithAssignedDescendants,
        ]);
    }
}
