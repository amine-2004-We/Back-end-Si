<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_notes', function (Blueprint $table) {
            if (Schema::hasColumn('project_notes', 'note')) {
                $table->dropColumn('note');
            }
        });
    }

    public function down(): void
    {
        Schema::table('project_notes', function (Blueprint $table) {
            if (!Schema::hasColumn('project_notes', 'note')) {
                $table->text('note')->nullable();
            }
        });
    }
};
