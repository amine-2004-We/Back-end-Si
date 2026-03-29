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
        $foreignKeyName = 'products_category_id_foreign';

        $constraintExists = DB::table('information_schema.table_constraints')
            ->where('table_name', 'products')
            ->where('constraint_name', $foreignKeyName)
            ->exists();

        if (!$constraintExists) {
            Schema::table('products', function (Blueprint $table) {
                $table->foreign('category_id')
                      ->references('id')
                      ->on('categories')
                      ->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
        });
    }
};
