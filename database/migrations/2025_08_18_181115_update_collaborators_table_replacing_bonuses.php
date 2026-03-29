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
        Schema::table('collaborators', function (Blueprint $table) {
            $table->dropColumn('bonuses');
            $table->decimal('cart', 10, 2)->nullable();
            $table->decimal('transportation', 10, 2)->nullable();
            $table->decimal('presentation', 10, 2)->nullable();
            $table->decimal('movement', 10, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('collaborators', function (Blueprint $table) {
            $table->decimal('bonuses', 10, 2)->nullable();
            $table->dropColumn('cart');
            $table->dropColumn('transportation');
            $table->dropColumn('presentation');
            $table->dropColumn('movement');
        });
    }
};
