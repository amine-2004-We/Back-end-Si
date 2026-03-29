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
        Schema::table('contact_people', function (Blueprint $table) {
            $table->string('contact_code')->unique()->nullable();
            $table->string('organisation')->nullable();
            $table->string('original_channel')->nullable();
            $table->date('acquisition_date')->nullable();
            $table->string('contact_status')->nullable();
            $table->boolean('option')->nullable();
            $table->date('consent_date')->nullable();
            $table->string('tags')->nullable();
            $table->boolean('api')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $table->dropColumn('contact_code');
        $table->dropColumn('organisation');
        $table->dropColumn('original_channel');
        $table->dropColumn('acquisition_date');
        $table->dropColumn('contact_status');
        $table->dropColumn('option');
        $table->dropColumn('consent_date');
        $table->dropColumn('tags');
        $table->dropColumn('api');

    }
};







