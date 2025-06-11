<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use App\Models\BusinessStructureNode;
use App\Models\KnownPlace;

// Correct model

class TestController extends Controller
{
    public function index()
    {
        // --- Data for Map ---
        // Select only necessary fields for the map
        $placesForMap = KnownPlace::select(['id', 'name', 'latitude', 'longitude', 'radius'])
            ->whereNotNull(['latitude', 'longitude']) // Ensure we have coordinates
            ->get()
            ->map(fn($place) => [ // Format for easy JS consumption
                'id' => $place->id,
                'name' => $place->name,
                'lat' => (float) $place->latitude, // Cast to float
                'lng' => (float) $place->longitude, // Cast to float
                'radius' => (int) $place->radius, // Cast to int
            ]);

        // --- Data for Coverage Chart (Using BusinessStructureNode) ---
        // Count LEAF Business Structure Nodes that HAVE associated Known Places
        // The whereIsLeaf() method comes from the NodeTrait (kalnoy/nestedset)
        $coveredNodesCount = BusinessStructureNode::whereIsLeaf()->has('knownPlaces')->count();

        // Count LEAF Business Structure Nodes that DO NOT HAVE associated Known Places
        $uncoveredNodesCount = BusinessStructureNode::whereIsLeaf()->doesntHave('knownPlaces')->count();

        $coverageData = [
            'covered' => $coveredNodesCount,
            'uncovered' => $uncoveredNodesCount,
        ];

        // You would add data fetching for other charts here...

        return view('test', [ // Pass data to the view
            'placesForMap' => $placesForMap,
            'coverageData' => $coverageData,
            // Pass other data variables here...
        ]);
    }
}
