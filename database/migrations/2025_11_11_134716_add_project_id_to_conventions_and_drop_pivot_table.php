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
         Schema::table('conventions', function (Blueprint $table) {
             if (!Schema::hasColumn('conventions', 'project_id')) {
                $table->foreignId('project_id')
                      ->nullable()  
                      ->constrained('projects')
                      ->onDelete('cascade')
                      ->after('partner_id');  
            }
        });

         Schema::dropIfExists('convention_project');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('conventions', function (Blueprint $table) {
            if (Schema::hasColumn('conventions', 'project_id')) {
                $table->dropForeign(['project_id']);
                $table->dropColumn('project_id');
            }
        });

        if (!Schema::hasTable('convention_project')) {
            Schema::create('convention_project', function (Blueprint $table) {
                $table->id();
                $table->foreignId('convention_id')->constrained('conventions')->onDelete('cascade');
                $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
                $table->timestamps();
            });
        }
    }
};