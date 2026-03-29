<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {

            $table->dropUnique(['article_id']);
            $table->string('article_id')->change();
        });
        Schema::table('articles', function (Blueprint $table) {
            $table->unique('article_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {

            $table->dropUnique(['article_id']);
            $table->unsignedBigInteger('article_id')->change();
        });
        Schema::table('articles', function (Blueprint $table) {
            $table->unique('article_id');
        });
    }
};

