<?php

namespace App\Jobs;

use App\Jobs\ccai\ExportConversationToGCSBucket;
use App\Jobs\SummarizeTranscript;
use App\Models\Inventory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class GeminiTranscribeAudio implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 0;

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
        $pythonEnv = config('settings.transcription_gemini.python_env');
        $script = config('settings.transcription_gemini.script');
        $gcsURL = $this->audio->disk_path;

        $process = new Process([$pythonEnv, $script, $gcsURL]);
        $process->setTimeout(60 * 30);

        Log::info("Transcription started for $gcsURL", [
            'audio' => $this->audio->toArray(),
        ]);

        try {
            $process->mustRun();
            $response = json_decode($process->getOutput(), true);

            Log::info("OK => Transcription finished for $gcsURL", [
                'response' => $response,
            ]);

            # Save transcription in database
            $this->audio->update([
                'transcription' => $response,
                'transcription_queued_at' => null,
                'model_used' => 'gemini',
            ]);

            # Perform sentiment analysis & customer experience tasks
            dispatch(new AnalyzeSentiment($this->audio->id));

            # Export transcription to GCS bucket
            dispatch(new ExportConversationToGCSBucket($this->audio->id));

            # Summarize conversation
            dispatch(new SummarizeTranscript($this->audio->id));

            # Topic extraction
            dispatch(new TopicExtraction($this->audio->id));
        } catch (ProcessFailedException $exception) {
            Log::error("Transcription failed for $gcsURL", [
                'error' => $exception->getMessage()
            ]);
        }
    }
}
