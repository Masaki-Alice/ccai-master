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
        Schema::table('data_inventory', function (Blueprint $table) {
            $table->json('gcs_metadata')->nullable()->after('redaction_requested_on');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('data_inventory', function (Blueprint $table) {
            $table->dropColumn('gcs_metadata');
        });
    }
};
