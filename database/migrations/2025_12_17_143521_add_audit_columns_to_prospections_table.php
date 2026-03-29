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
            // Add audit columns if they don't exist
            if (!Schema::hasColumn('prospections', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
                $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('prospections', 'updated_at')) {
                $table->timestamp('updated_at')->nullable()->after('updated_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prospections', function (Blueprint $table) {
            if (Schema::hasColumn('prospections', 'updated_by')) {
                $table->dropForeign(['updated_by']);
                $table->dropColumn('updated_by');
            }
            
            if (Schema::hasColumn('prospections', 'updated_at')) {
                $table->dropColumn('updated_at');
            }
        });
    }
};
