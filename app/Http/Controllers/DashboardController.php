<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    public function index()
    {
        // TODO: Come back and add Locations relationship to this once we fix the locations table
        $user = auth()->user()->load('types', 'knownPlaces')->loadCount(['types', 'knownPlaces']);

        return view('dashboard', compact('user'));
    }
}
