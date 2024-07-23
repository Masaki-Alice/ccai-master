<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TranscriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $inventory = Inventory::with('slices')->findOrFail($id);

        return Inertia::render('Transcriptions/Preview', compact('inventory'));

        // dd($inventory->toArray());
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $inventory = Inventory::with('slices')->findOrFail($id);

        return Inertia::render('Inventory/View', compact('inventory'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function saveGroundTruth(Request $request, $id)
    {
        $request->validate([
            'ground_truth' => 'required'
        ]);

        $audio = Inventory::findOrFail($id);
        $audio->update([
            'ground_truth' => $request->ground_truth
        ]);

        return redirect()->back()->with('success', 'Your transcript has been saved');
    }

    /**
     * Remove the specified resource from storage.
     */





}
