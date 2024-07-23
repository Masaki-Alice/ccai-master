<?php

namespace App\Console\Commands;

use App\Jobs\GeminiTranscribeAudio;
use App\Jobs\TranslateTranscriptionWithGemini;
use App\Models\AudioSlice;
use App\Models\Inventory;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;


class GeminiTranscription extends Command implements PromptsForMissingInput
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ccai:gemini {dbID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transcribe bucket audio with Gemini';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // $slice = AudioSlice::find($this->argument('dbID'));
        // dispatch_sync(new GeminiTranscribeAudio($slice->id, 'slice'));

        $audio = Inventory::find($this->argument('dbID'));
        dispatch_sync(new TranslateTranscriptionWithGemini($audio->id));
    }

    /**
     * Prompt for missing input arguments using the returned questions.
     *
     * @return array<string, string>
     */
    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'dbID' => 'Enter the DB ID of the slice you wish to transcribe:',
        ];
    }
}
