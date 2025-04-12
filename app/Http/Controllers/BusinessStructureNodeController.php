<?php

namespace App\Http\Controllers;

use App\Models\BusinessStructureNode;
use App\Models\BusinessStructureType;
use Illuminate\Http\Request;

class BusinessStructureNodeController extends Controller
{
    public function index(Request $request)
    {
        $types = BusinessStructureType::all();
        // Get the entire tree with a single query, ordered hierarchically
        $nodes = BusinessStructureNode::with('type')->withDepth()->defaultOrder()->get()->toTree();

        return view('business-structure.locations.index', [
            'nodes' => $nodes,
            'types' => $types,
        ]);
    }
}
