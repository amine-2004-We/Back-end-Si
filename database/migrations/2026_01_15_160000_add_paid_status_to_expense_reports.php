<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add 'paid' status to expense_reports table
     */
    public function up(): void
    {
        // Drop the existing check constraint and add a new one with 'paid' status
        DB::statement("ALTER TABLE expense_reports DROP CONSTRAINT IF EXISTS expense_reports_status_check");
        
        DB::statement("ALTER TABLE expense_reports ADD CONSTRAINT expense_reports_status_check CHECK (status IN ('created', 'submitted', 'validated_manager', 'validated_treasury', 'validated_accounting', 'rejected', 'paid'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First update any 'paid' status back to 'validated_accounting' 
        DB::statement("UPDATE expense_reports SET status = 'validated_accounting' WHERE status = 'paid'");
        
        // Drop the constraint and recreate without 'paid'
        DB::statement("ALTER TABLE expense_reports DROP CONSTRAINT IF EXISTS expense_reports_status_check");
        
        DB::statement("ALTER TABLE expense_reports ADD CONSTRAINT expense_reports_status_check CHECK (status IN ('created', 'submitted', 'validated_manager', 'validated_treasury', 'validated_accounting', 'rejected'))");
    }
};
