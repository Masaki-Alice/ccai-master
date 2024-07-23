<?php

namespace App\Library;

use App\Models\Inventory;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Support\Facades\Storage;

class GoogleBucket
{
    public static function initialize()
    {
        $storage = new StorageClient([
            'projectId' => 'ccai-dev-project',
            'keyFile' => json_decode(Storage::get('bucket_access_key.json'), true),
        ]);

        return $storage;
    }

    public static function listBucket($bucketName)
    {
        $files = [];
        $storage = GoogleBucket::initialize();
        $bucket = $storage->bucket($bucketName);

        foreach ($bucket->objects() as $object) {
            $files[] = [
                'bucket_url' => $object->gcsUri(),
                'metadata' => $object->info(),
            ];
        }

        return $files;
    }

    public static function downloadFile($bucketName, $fileName, $destination)
    {
        $storage = GoogleBucket::initialize();
        $bucket = $storage->bucket($bucketName);

        $object = $bucket->object($fileName);
        $contents = $object->downloadAsString();

        Storage::disk('public')->put("tmp/{$fileName}", $contents);
    }

    /**
     * Synchronizes the files in the GCS bucket
     *
     * This function retrieves the list of files from the source materials bucket using the
     * `listBucket` method of the `GoogleBucket` class. It then iterates over each file and
     * creates or updates the corresponding inventory record in the database.
     *
     * @return boolean
     */
    public static function sync()
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

        return true;
    }
}
