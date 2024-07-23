<?php

namespace App\Console\Commands;

use App\Models\Inventory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MakeInventory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ccai:make-inventory {path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inventory dataset files from a disk path';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = $this->argument('path');

        collect(scandir($path))->each(function ($file) use ($path) {
            $exclude = ['.', '..'];
            if (!in_array($file, $exclude)) {
                Inventory::create([
                    'file_name' => $file,
                    'disk_path' => realpath("{$path}/{$file}"),
                ]);
                $this->info("{$file} has been indexed.");
            }
        });
    }
}
