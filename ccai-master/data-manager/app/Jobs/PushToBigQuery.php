<?php

namespace App\Jobs;

use App\Models\Inventory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class PushToBigQuery implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $audio;

    /**
     * Create a new job instance.
     */
    public function __construct($id)
    {
        $this->audio = Inventory::findOrFail($id);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $pythonEnv = config('settings.bigquery.python_env');
        $script = config('settings.bigquery.script');
        $bqTable = config('settings.bigquery.table');
        $payload = base64_encode(json_encode($this->audio->toArray()));

        $process = new Process([$pythonEnv, $script, $payload, $bqTable]);
        $process->setTimeout(60 * 30);

        Log::info("BigQuery sync started for {$this->audio->disk_path}", [
            'payload' => $this->audio->toArray()
        ]);

        try {
            $process->mustRun();
            $response = json_decode($process->getOutput(), true);

            Log::info("OK => BigQuery sync completed for {$this->audio->disk_path}", [
                'response' => $response
            ]);

            if ($response['status'] == 'OK') {
                $this->audio->update([
                    'bigquery_id' => $response['bigquery_id']
                ]);
            } else {
                dd($response['error']);
            }

            // dd($response);
        } catch (ProcessFailedException $exception) {
            print($exception->getMessage());
            dd('Bigquery died!');
        }
    }
}
