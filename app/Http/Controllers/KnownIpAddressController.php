<?php

namespace App\Http\Controllers;

use App\Models\KnownIpAddress;
use Log;
use LogicException;

class KnownIpAddressController extends Controller
{
    public function destroy(KnownIpAddress $knownIpAddress)
    {
        try {
            $knownIpAddress->delete();
        } catch (LogicException $e) {
            Log::error("KnownIpAddressController: 'knownIpAddress $knownIpAddress' failed to be deleted: ".$e);
            return back()->with('error', 'Failed to delete known IP address.');
        } finally {
            return back()->with('success', 'Known IP Address deleted.');
        }
    }

    public function index()
    {
        $ipAddresses = auth()->user()->knownIpAddresses()->paginate();
        return view('known-ip-addresses.index', compact('ipAddresses'));
    }


}
