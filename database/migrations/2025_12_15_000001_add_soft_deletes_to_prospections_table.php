<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prospections', function (Blueprint $table) {
            // Add soft deletes column if it does not already exist
            if (!Schema::hasColumn('prospections', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('prospections', function (Blueprint $table) {
            if (Schema::hasColumn('prospections', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};

