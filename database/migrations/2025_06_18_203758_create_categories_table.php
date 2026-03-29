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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();  // PK technique
            $table->unsignedBigInteger('category_id')->unique(); // UUID métier
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement("create unique index categories_name_unique on categories (name) where deleted_at is null");

              

    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
        });
        DB::statement("DROP INDEX IF EXISTS categories_name_unique");

        Schema::dropIfExists('categories');
    }
};
