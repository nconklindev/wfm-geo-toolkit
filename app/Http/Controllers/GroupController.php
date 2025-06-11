<?php

namespace App\Http\Controllers;

use App\Models\Group;

class GroupController extends Controller
{
    public function create()
    {
        //
    }

    public function destroy(Group $group)
    {
        $group->delete();

        return redirect()->route('groups.index')->with('success', 'Group deleted successfully');
    }

    public function edit(Group $group)
    {
        return view('groups.edit', compact('group'));
    }

    public function index()
    {
        $groups = auth()->user()->groups()->paginate();
        return view('groups.index', compact('groups'));
    }

    public function show(Group $group)
    {
        return view('groups.show', compact('group'));
    }
}
