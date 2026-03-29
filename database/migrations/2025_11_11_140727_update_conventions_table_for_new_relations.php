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
        // 1. Drop the old pivot table if it exists
        Schema::dropIfExists('convention_project');

        // 2. Add all missing columns to the 'conventions' table
        Schema::table('conventions', function (Blueprint $table) {
            
            // Add project_id
            if (!Schema::hasColumn('conventions', 'project_id')) {
                $table->foreignId('project_id')
                      ->nullable() // Make nullable to avoid errors on existing data
                      ->constrained('projects')
                      ->onDelete('set null') // Use 'set null' or 'cascade'
                      ->after('partner_id');
            }

            // ✅ ADDED: Add duration_months
            if (!Schema::hasColumn('conventions', 'duration_months')) {
                // We make it nullable because the original migration didn't have it.
                // Your app logic (via StoreConventionRequest) should require it.
                $table->integer('duration_months')->nullable()->after('signed_at');
            }

            // ✅ ADDED: Add responsible_id
            if (!Schema::hasColumn('conventions', 'responsible_id')) {
                $table->foreignId('responsible_id')
                      ->nullable() // Make nullable to avoid errors
                      ->constrained('collaborators')
                      ->onDelete('set null')
                      ->after('estimated_end_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Remove the new columns from 'conventions'
        Schema::table('conventions', function (Blueprint $table) {
            if (Schema::hasColumn('conventions', 'project_id')) {
                $table->dropForeign(['project_id']);
                $table->dropColumn('project_id');
            }
            if (Schema::hasColumn('conventions', 'duration_months')) {
                $table->dropColumn('duration_months');
            }
            if (Schema::hasColumn('conventions', 'responsible_id')) {
                $table->dropForeign(['responsible_id']);
                $table->dropColumn('responsible_id');
            }
        });

        // 2. Re-create the old pivot table
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