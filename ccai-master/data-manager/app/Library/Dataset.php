<?php

namespace App\Library;

use App\Exports\DatasetExport;
use App\Models\AudioSlice;

use Maatwebsite\Excel\Excel as ExcelObject;
use Maatwebsite\Excel\Facades\Excel;
use \Zip;

class Dataset
{
    static function slices()
    {
        $rows = [];
        $slices = AudioSlice::whereNotNull('transcription')->get();

        $slices->each(function ($slice) use (&$rows) {
            # Prefer ground truth first, if it exists
            if ($slice->ground_truth) {
                $rows[] = $slice;
            } else {
                # Transcript should not be an empty object
                if (count(@$slice->transcription['transcript'])) {
                    $rows[] = $slice;
                }
            }
        });

        return collect($rows);
    }

    static function buildArchive()
    {
        $slices = Dataset::slices();

        # Build zip file
        $zip = Zip::create('swanglish.zip');
        $slices->each(function ($slice) use (&$zip) {
            $zip = $zip->add("{$slice->disk_path}", "data/{$slice->file_name}");
        });

        # Build CSV metadata file
        $metadata = Excel::raw(new DatasetExport, ExcelObject::CSV);
        $zip = $zip->addRaw($metadata, 'metadata.csv');

        return $zip;
    }
}
