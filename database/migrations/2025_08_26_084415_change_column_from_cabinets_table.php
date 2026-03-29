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
        Schema::table('cabinets', function (Blueprint $table) {
            $table->dropForeign(['responsible_id']);

            $table->renameColumn('responsible_id', 'responsible_name');
        });

        Schema::table('cabinets', function (Blueprint $table) {
            $table->string('responsible_name')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cabinets', function (Blueprint $table) {
            $table->integer('responsible_name')->change();
            $table->renameColumn('responsible_name', 'responsible_id');

            $table->foreign('responsible_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
