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
            $table->string('loudness')->nullable()->after('disk_path');
            $table->string('channels')->nullable()->after('disk_path');
            $table->string('sample_width')->nullable()->after('disk_path');
            $table->string('frame_rate')->nullable()->after('disk_path');
            $table->string('length_in_seconds')->nullable()->after('disk_path');
            $table->string('split_count')->nullable()->after('disk_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('data_inventory', function (Blueprint $table) {
            $table->dropColumn('loudness');
            $table->dropColumn('channels');
            $table->dropColumn('sample_width');
            $table->dropColumn('frame_rate');
            $table->dropColumn('length_in_seconds');
            $table->dropColumn('split_count');
        });
    }
};
