<?php

namespace App\Jobs;

use App\Library\DLPService;
use App\Models\Inventory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RedactTranscript implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $inventory;

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
        # Perform analysis of risky fields and redact transcript
        $risk = DLPService::inspect(config('settings.dlp.project_id'), $this->inventory->text);
        $redacted = DLPService::redact(config('settings.dlp.project_id'), $this->inventory->text);
        Log::info('Risk analysis: ' . $redacted);

        $this->inventory->update([
            'redacted_transcript' => $redacted,
            'dlp_risk_analysis' => $risk,
            'redaction_requested_on' => null
        ]);
    }

    public function failed(): void
    {
        $this->inventory->update([
            'redaction_requested_on' => null
        ]);
    }
}
