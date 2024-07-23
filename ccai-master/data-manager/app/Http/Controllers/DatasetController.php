<?php

namespace App\Http\Controllers;

use App\Exports\DatasetExport;
use App\Library\Dataset;
use App\Library\GoogleBucket;
use App\Models\AudioSlice;
use App\Models\Inventory;
use Maatwebsite\Excel\Excel as ExcelExcel;
use Maatwebsite\Excel\Facades\Excel;
use \Zip;

class DatasetController extends Controller
{

    function syncFromBucket()
    {
        $files = GoogleBucket::listBucket(config('settings.buckets.source_materials'));

        collect($files)->each(function ($file) {
            $payload = [
                'file_name' => $file['metadata']['name'],
                'disk_path' => $file['bucket_url'],
                'gcs_metadata' => $file['metadata']
            ];

            $inventory = Inventory::where('file_name', $file['metadata']['name'])->first();

            if ($inventory) {
                $inventory->update($payload);
            } else {
                Inventory::create($payload);
            }
        });

        return redirect()->back()->with('success', "Google Cloud bucket (" . config('settings.buckets.source_materials') . ") has been successfully synced");
    }

    /**
     * Export the dataset by creating a zip file with audio slices and a manifest file.
     *
     * @return Zip The created zip file containing the dataset
     */
    function export()
    {
        $zip = Dataset::buildArchive();
        // $zip->saveTo('../public');

        // $url = asset('swanglish.zip');
        // return redirect()->back()->with('success', "You dataset has been exported to {$url}");

        return $zip;
    }
}
