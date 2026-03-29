<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_notes', function (Blueprint $table) {
             if (!Schema::hasColumn('project_notes', 'user_id')) {
                $table->foreignId('user_id')->after('id')->constrained('users');
            }

             if (!Schema::hasColumn('project_notes', 'project_id')) {
                $table->foreignId('project_id')->after('id')->constrained('projects')->onDelete('cascade');
            }

             if (!Schema::hasColumn('project_notes', 'content')) {
                $table->text('content');
            }
        });
    }

    public function down(): void
    {
        Schema::table('project_notes', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};

