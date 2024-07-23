<?php

namespace App\Console\Commands;

use Google\Cloud\Storage\StorageClient;
use Illuminate\Console\Command;

class BucketTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ccai:bucket';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $storage = new StorageClient();
        $bucket = $storage->bucket(config('settings.buckets.source_materials'));

        dump(config('settings.buckets.source_materials'));

        foreach ($bucket->objects() as $object) {
            dd($object->name());
        }
    }
}
