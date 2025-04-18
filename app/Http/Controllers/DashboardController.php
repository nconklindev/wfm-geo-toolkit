<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user()->load('knownPlaces')->loadCount([
            'knownPlaces',
        ]);

        $leafNodes = $user->nodes()
            ->whereIsLeaf()
            ->withCount('knownPlaces')
            ->with('knownPlaces')
            ->orderByDesc('created_at')
            ->get();

        return view('dashboard', compact('user', 'leafNodes'));
    }
}
