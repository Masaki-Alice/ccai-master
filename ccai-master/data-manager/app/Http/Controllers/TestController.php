<?php

namespace App\Http\Controllers;

use App\Jobs\PushToBigQuery;
use App\Library\GoogleBucket;
use App\Models\Inventory;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Jobs\GeminiTranscribeAudio;

class TestController extends Controller
{
    function dlpTest()
    {
        return view('dlp-test');
    }

    function bucket()
    {
        $files = GoogleBucket::listBucket(config('settings.buckets.source_materials'));

        dd($files);
    }

    function bigquery()
    {
        $inventory = Inventory::find(1);

        dispatch_sync(new GeminiTranscribeAudio($inventory->id));
    }
}
