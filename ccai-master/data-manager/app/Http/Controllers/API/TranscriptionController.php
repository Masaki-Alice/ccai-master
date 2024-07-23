<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\TranscriptionRequest;
use App\Jobs\DiarizeAudio;
use App\Jobs\GeminiTranscribeAudio;
use App\Jobs\TranslateTranscriptionWithGemini;
use App\Library\GoogleBucket;
use App\Models\Inventory;
use Illuminate\Support\Facades\Log;

class TranscriptionController extends Controller
{
    function transcribeGCSAudio(TranscriptionRequest $request)
    {
        Log::info("Cloud Function Received", $request->all());

        $gsLink = $request->gs_link;

        # Check audio has been synced from bucket
        $audio = Inventory::where('disk_path', $gsLink)->first();
        if (!$audio) {
            GoogleBucket::sync();
            $audio = Inventory::where('disk_path', $gsLink)->first();
        }

        # Do not attempt transcription of a file that cannot be found
        if (!$audio) {
            $msg = "Cannot transcribe. Check audio exists in Google Cloud Storage";
            Log::error($msg);
            return response()->json([
                'error' => $msg
            ], 400);
        }

        # Dispatch transcription job
        dispatch(new GeminiTranscribeAudio($audio->id));

        # Dispatch translation job
        dispatch(new TranslateTranscriptionWithGemini($audio->id));

        # Dispatch diarization job
        dispatch(new DiarizeAudio($audio->id));
    }
}
