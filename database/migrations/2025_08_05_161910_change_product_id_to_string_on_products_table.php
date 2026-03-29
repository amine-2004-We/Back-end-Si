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
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['product_id']);
            $table->string('product_id')->change();
        });
        Schema::table('products', function (Blueprint $table) {
            $table->unique('product_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['product_id']);
            $table->unsignedBigInteger('product_id')->change();
        });
        Schema::table('products', function (Blueprint $table) {
            $table->unique('product_id');
        });
    }
};
