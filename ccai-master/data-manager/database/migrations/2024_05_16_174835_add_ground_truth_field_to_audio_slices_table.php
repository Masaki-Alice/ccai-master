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
        Schema::table('audio_slices', function (Blueprint $table) {
            $table->text('ground_truth')->nullable()->after('transcription');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audio_slices', function (Blueprint $table) {
            $table->dropColumn('ground_truth');
        });
    }
};
