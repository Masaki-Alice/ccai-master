<?php

namespace App\Console\Commands;

use App\Jobs\PushToBigQuery;
use App\Models\Inventory;
use Illuminate\Console\Command;

class TestBigquery extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ccai:bigquery {id}';

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
        $audio = Inventory::find($this->argument('id'));

        print((json_encode($audio->toArray())));

        // dd($audio);
        dispatch_sync(new PushToBigQuery($audio->id));
    }
}
