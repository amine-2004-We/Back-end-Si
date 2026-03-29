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
        Schema::table('prospections', function (Blueprint $table) {
            if (!Schema::hasColumn('prospections', 'prospector_id')) {
                $table->unsignedBigInteger('prospector_id')->nullable()->after('site_id');
                $table->foreign('prospector_id')->references('id')->on('collaborators')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prospections', function (Blueprint $table) {
            if (Schema::hasColumn('prospections', 'prospector_id')) {
                $table->dropForeign(['prospector_id']);
                $table->dropColumn('prospector_id');
            }
        });
    }
};
