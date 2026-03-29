<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('conventions', function (Blueprint $table) {
            DB::statement('ALTER TABLE conventions DROP CONSTRAINT IF EXISTS conventions_status_check;');

            DB::statement('ALTER TABLE conventions DROP CONSTRAINT IF EXISTS conventions_type_check;');
         });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
