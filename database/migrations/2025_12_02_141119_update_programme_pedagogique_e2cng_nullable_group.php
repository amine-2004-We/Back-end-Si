<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programme_pedagogique_e2cng', function (Blueprint $table) {
            $table->foreignId('groupe_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        // $table->foreignId('groupe_id')->nullable(false)->change();
    }
};