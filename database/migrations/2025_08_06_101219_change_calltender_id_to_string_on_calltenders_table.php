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
        Schema::table('calltenders', function (Blueprint $table) {
            //
            $table->dropUnique(['calltender_id']);
            $table->string('calltender_id')->change();

        });

        Schema::table('calltenders', function (Blueprint $table) {
            $table->unique('calltender_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calltenders', function (Blueprint $table) {
            //
            $table->dropUnique(['calltender_id']);
            $table->unsignedBigInteger('calltender_id')->change();
        });

        Schema::table('calltenders', function (Blueprint $table) {
            $table->unique('calltender_id');
        });
    }
};
