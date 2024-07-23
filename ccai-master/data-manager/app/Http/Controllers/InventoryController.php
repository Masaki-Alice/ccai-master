<?php

namespace App\Http\Controllers;

use App\Jobs\GeminiTranscribeAudio;
use App\Jobs\RedactTranscript;
use App\Jobs\SliceAudio;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Jobs\TranscribeAudio;
use App\Library\GoogleBucket;
use App\Library\Transcription;
use Illuminate\Support\Facades\Storage;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $inventory = Inventory::with('slices');

        if (request()->q) {
            $inventory = $inventory->where('file_name', 'LIKE', '%' . request()->q . '%');
        }

        $inventory = $inventory->paginate(100);

        return Inertia::render('Inventory/List', compact('inventory'));
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

        GoogleBucket::downloadFile(config('settings.buckets.source_materials'), $inventory->file_name, 'tmp');

        return Inertia::render('Inventory/View', compact('inventory'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    function transcribeAudio($audioID, $audioType)
    {
        $audio = Transcription::findAudio($audioID, $audioType);
        if (!$audio) {
            return redirect()->back()->with('error', 'The specified audio could not be found');
        }

        dispatch(new TranscribeAudio($audio->id, $audioType));
        $audio->update(['transcription_queued_at' => now()]);

        return redirect()->back()->with('success', 'Transcription job has been queued. Once the job is complete, you will see your results on this page');
    }

    function sliceAudio($inventoryID)
    {
        $inventory = Inventory::findOrFail($inventoryID);

        dispatch(new SliceAudio($inventory->id));

        $inventory->update(['slicing_queued_at' => now()]);

        return redirect()->route('inventory.show', $inventory->id)
            ->with('success', 'Slice job has been queued. Once the job is complete, you will see your results on this page');
    }

    public function streamAudio($audioID, $audioType)
    {
        $inventory = Inventory::find($audioID);
        $file = Storage::disk('public')->get("tmp/{$inventory->file_name}");

        return response($file, 200)->header('Content-Type', 'audio/mpeg');
    }

    function suggestWithGemini($id)
    {
        $inventory = Inventory::findOrFail($id);

        dispatch_sync(new GeminiTranscribeAudio($inventory->id));

        return redirect()->back()->with('success', 'Suggestion saved');
    }

    function redactTranscript($id)
    {
        $inventory = Inventory::findOrFail($id);

        dispatch(new RedactTranscript($inventory->id));

        $inventory->update([
            'redaction_requested_on' => now()
        ]);

        return redirect()->back();
    }
}
