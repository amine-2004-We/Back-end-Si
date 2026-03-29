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
            // Status column
            $table->string('validation_status')->default('draft')->after('prospector_id');
            
            // Prospector role type (for determining validation workflow)
            $table->string('prospector_role_type')->nullable()->after('validation_status');
            // Possible values: 'superviseur', 'teacher', 'other'
            
            // === VALIDATION COLUMNS ===
            // For all workflows
            $table->text('national_observations')->nullable()->after('prospector_role_type');
            
            // For Superviseur workflow (3 levels) and Teacher workflow (4 levels)
            $table->text('operational_observations')->nullable();
            $table->text('regional_observations')->nullable();
            
            // For Teacher workflow only (4 levels)
            $table->text('supervisor_observations')->nullable();
            
            // === REVIEWER TRACKING ===
            // Supervisor (only for teacher workflow)
            $table->foreignId('supervisor_reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('supervisor_reviewed_at')->nullable();
            
            // Operational (Superviseur workflow level 1, Teacher workflow level 2)
            $table->foreignId('operational_reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('operational_reviewed_at')->nullable();
            
            // Regional (Superviseur workflow level 2, Teacher workflow level 3)
            $table->foreignId('regional_reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('regional_reviewed_at')->nullable();
            
            // National (Final reviewer for all workflows)
            $table->foreignId('national_reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('national_reviewed_at')->nullable();
            
            // Rejection tracking
            $table->boolean('is_rejected')->default(false);
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            
            // Index for efficient querying
            $table->index('validation_status');
            $table->index('prospector_role_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prospections', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeignKeyIfExists('prospections_supervisor_reviewer_id_foreign');
            $table->dropForeignKeyIfExists('prospections_operational_reviewer_id_foreign');
            $table->dropForeignKeyIfExists('prospections_regional_reviewer_id_foreign');
            $table->dropForeignKeyIfExists('prospections_national_reviewer_id_foreign');
            
            // Drop columns
            $table->dropColumn([
                'validation_status',
                'prospector_role_type',
                'supervisor_observations',
                'operational_observations',
                'regional_observations',
                'national_observations',
                'supervisor_reviewer_id',
                'supervisor_reviewed_at',
                'operational_reviewer_id',
                'operational_reviewed_at',
                'regional_reviewer_id',
                'regional_reviewed_at',
                'national_reviewer_id',
                'national_reviewed_at',
                'is_rejected',
                'rejected_at',
                'rejection_reason',
            ]);
            
            // Drop indexes
            $table->dropIndex(['validation_status']);
            $table->dropIndex(['prospector_role_type']);
        });
    }
};
