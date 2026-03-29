<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;


return new class extends Migration {
    public function up(): void
    {
        DB::statement('ALTER TABLE collaborators ALTER COLUMN annual_leave_days TYPE FLOAT USING annual_leave_days::FLOAT');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE collaborators ALTER COLUMN annual_leave_days TYPE INTEGER USING annual_leave_days::INTEGER');
    }
};
