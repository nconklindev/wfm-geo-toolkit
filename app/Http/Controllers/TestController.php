<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class TestController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        foreach ($user->businessStructureNodes() as $node) {
            dd($user->businessStructureNodes());
        }
    }
}
