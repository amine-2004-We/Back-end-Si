<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
    public function up(): void
    {
        DB::statement('ALTER TABLE leave ALTER COLUMN nbr_days TYPE FLOAT USING nbr_days::FLOAT');
        Schema::table('leave', function (Blueprint $table) {
            $table->string('start_time')->nullable();
            $table->string('end_time')->nullable();
        });
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE leave ALTER COLUMN nbr_days TYPE INTEGER USING nbr_days::INTEGER');
        Schema::table('leave', function (Blueprint $table) {
            $table->dropColumn('start_time');
            $table->dropColumn('end_time');
        });
    }
};
