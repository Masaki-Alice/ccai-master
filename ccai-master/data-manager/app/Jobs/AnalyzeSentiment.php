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

class AnalyzeSentiment implements ShouldQueue
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
        $pythonEnv = config('settings.sentiment_analysis.python_env');
        $script = config('settings.sentiment_analysis.script');

        # Build payloads
        $payload = base64_encode(json_encode($this->audio->transcription['transcript']));
        $agentPayload = base64_encode(json_encode(collect($this->audio->transcription['transcript'])->where('speaker', 'AGENT')->toArray()));
        $customerPayload = base64_encode(json_encode(collect($this->audio->transcription['transcript'])->where('speaker', 'CUSTOMER')->toArray()));

        # Run sentiment analysis
        $analysisResults = [
            'complete' => $this->analyzeTranscript($pythonEnv, $script, $payload, 'Full'),
            'agent_only' => $this->analyzeTranscript($pythonEnv, $script, $agentPayload, 'Agent only'),
            'customer_only' => $this->analyzeTranscript($pythonEnv, $script, $customerPayload, 'Customer only')
        ];

        # Save sentiment in database
        $this->audio->update([
            'sentiment_analysis' => $analysisResults,
        ]);
    }

    function analyzeTranscript($pythonEnv, $script, $payload, $type)
    {
        $process = new Process([$pythonEnv, $script, $payload]);
        $process->setTimeout(60 * 30);
        $analysis = null;

        Log::info("START => {$type} sentiment analysis started for {$this->audio->disk_path}", [
            'payload' => base64_decode(json_decode($payload))
        ]);

        try {
            $process->mustRun();
            $response = json_decode($process->getOutput(), true);

            Log::info("OK => {$type} sentiment analysis finished for {$this->audio->disk_path}", [
                'response' => $response,
            ]);

            $analysis = $response;
        } catch (ProcessFailedException $exception) {
            Log::error("FAIL => {$type} sentiment analysis failed for {$this->audio->disk_path}", [
                'error' => $exception->getMessage(),
                'payload' => base64_decode(json_decode($payload, true))
            ]);
        }

        return $analysis;
    }
}
