<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('audio_slices', function (Blueprint $table) {
            $table->id();
            $table->integer('inventory_id');
            $table->text('file_name');
            $table->text('disk_path');
            $table->json('transcription')->nullable();
            $table->dateTime('transcription_queued_at')->nullable();
            $table->dateTime('slicing_queued_at')->nullable();
            $table->text('redacted_transcript')->nullable();
            $table->json('dlp_risk_analysis')->nullable();
            $table->dateTime('redaction_requested_on')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audio_slices');
    }
};
