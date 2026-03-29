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
        DB::statement("
        ALTER TABLE leave
        DROP CONSTRAINT IF EXISTS leave_status_check
    ");

        DB::statement("
        ALTER TABLE leave
        ADD CONSTRAINT leave_status_check
        CHECK (status IN (
            'pending',
            'approved_by_manager',
            'approved_by_hr',
            'rejected',
            'approved'
        ))
    ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
