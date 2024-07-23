<?php

namespace App\Jobs;

use App\Models\AudioSlice;
use App\Models\Inventory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class SliceAudio implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $inventory;
    public $timeout = 0;
    public $tries = 1;

    /**
     * Create a new job instance.
     */
    public function __construct($id)
    {
        $this->inventory = Inventory::findOrFail($id);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $pythonEnv = config('settings.slicer.python_env');
        $script = config('settings.slicer.script');
        $destination = config('settings.slicer.destination_folder');

        $process = new Process([
            $pythonEnv,
            $script,
            $this->inventory->disk_path,
            $destination
        ]);
        $process->setTimeout(60 * 30);

        try {
            $process->mustRun();

            $output = json_decode($process->getOutput());

            # Update parent stats
            $this->inventory->update([
                'loudness' => $output->loudness,
                'channels' => $output->channels,
                'sample_width' => $output->sample_width,
                'frame_rate' => $output->frame_rate,
                'length_in_seconds' => $output->length_in_seconds,
                'num_splits' => $output->num_splits,
                'slicing_queued_at' => null
            ]);

            # Save stored slices
            $rows = [];
            collect($output->segments)->each(function ($segment) use (&$rows) {
                $rows[] = [
                    'inventory_id' => $this->inventory->id,
                    'file_name' => basename($segment),
                    'disk_path' => $segment
                ];
                print("OK => Sliced " . basename($this->inventory->disk_path));
            });
            # Delete previous DB values
            AudioSlice::where('inventory_id', $this->inventory->id)->delete();
            AudioSlice::insert($rows);

            # Transcribe each slice
            AudioSlice::where('inventory_id', $this->inventory->id)->each(function ($slice) {
                dispatch(new TranscribeAudio($slice->id, 'slice'));
            });
            print('DONE');
        } catch (ProcessFailedException $exception) {
            dump($exception->getMessage());
        }
    }

    public function failed(): void
    {
        $this->inventory->update([
            'slicing_queued_at' => null
        ]);
    }
}
