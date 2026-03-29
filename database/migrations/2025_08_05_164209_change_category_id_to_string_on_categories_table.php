<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['category_id']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->string('category_id')->change();
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->unique('category_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('category_id')->nullable()->change();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreign('category_id')
                  ->references('category_id')
                  ->on('categories')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['category_id']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->change();
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->unique('category_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable()->change();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreign('category_id')
                  ->references('category_id')
                  ->on('categories')
                  ->onDelete('set null');
        });
    }
};
