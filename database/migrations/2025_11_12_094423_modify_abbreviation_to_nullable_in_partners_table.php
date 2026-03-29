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
        Schema::table('partners', function (Blueprint $table) {

            $table->string('abbreviation', 5)->nullable()->change();
            $table->string('phone', 30)->nullable()->change();
            $table->string('email', 255)->nullable()->change();
            $table->string('address')->nullable()->change();
            $table->string('country', 255)->nullable()->change();

            $table->unsignedBigInteger('nature_partner_id')->nullable()->change();
            $table->unsignedBigInteger('structure_partner_id')->nullable()->change();
            $table->string('partner_type')->nullable()->change();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->string('abbreviation', 5)->nullable(false)->change();
            $table->string('phone', 30)->nullable(false)->change();
            $table->string('email', 255)->nullable(false)->change();
            $table->string('address')->nullable(false)->change();
            $table->string('country', 255)->nullable(false)->change();

            $table->unsignedBigInteger('nature_partner_id')->nullable(false)->change();
            $table->unsignedBigInteger('structure_partner_id')->nullable(false)->change();
            $table->string('partner_type')->nullable(false)->change();
        });
    }
};
