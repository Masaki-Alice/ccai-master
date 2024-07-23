<?php

namespace App\Console\Commands;

use App\Exports\DatasetExport;
use App\Models\AudioSlice;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Excel as ExcelExcel;
use Maatwebsite\Excel\Facades\Excel;
use \Zip;

class BuildDataset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ccai:build-dataset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build training dataset';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        dd(count(AudioSlice::find(25304)->transcription['transcript']));
        # Build zip file contents
        $zipContents = [];
        AudioSlice::whereNotNull('transcription')->get()->each(function ($slice) use (&$zipContents) {
            # Drop out empty transcriptions too (music, noise, gibberish slices)
            if (@count($slice->transcription['transcript'])) {
                $zipContents[$slice->disk_path] = "data/{$slice->file_name}";
            }
        });

        # Generate manifest file
        $manifest = Excel::raw(new DatasetExport, ExcelExcel::CSV);

        # Merge audio dataset and manifest file
        $zipName = 'swanglish.zip';
        $zip = Zip::create($zipName, $zipContents);
        $zip = $zip->addRaw($manifest, 'metadata.csv');

        # Save dataset file to disk
        $zip->saveTo("public/{$zipName}");
    }
}
