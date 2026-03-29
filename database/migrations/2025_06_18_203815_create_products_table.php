<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id(); // PK technique
            $table->unsignedBigInteger('product_id')->unique(); // UUID métier
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')
                ->references('category_id')
                ->on('categories');
            $table->unsignedBigInteger('product_type_id');
            $table->foreign('product_type_id')
                ->references('id')
                ->on('product_types');
        });
        DB::statement("create unique index products_name_unique on products (name) where deleted_at is null");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
        DB::statement("DROP INDEX IF EXISTS products_name_unique");

    }
};
