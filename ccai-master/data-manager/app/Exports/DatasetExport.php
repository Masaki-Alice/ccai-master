<?php

namespace App\Exports;

use App\Library\Dataset;
use Maatwebsite\Excel\Concerns\FromCollection;

class DatasetExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $slices = [];
        $slices[] = [
            'file_name',
            'transcription',
        ];

        $data = Dataset::slices();

        $data->each(function ($slice) use (&$slices) {
            $slices[] = [
                "data/{$slice->file_name}",
                strtolower($slice->html),
            ];
        });

        return collect($slices);
    }
}
