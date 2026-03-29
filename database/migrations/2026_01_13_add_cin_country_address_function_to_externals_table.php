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
        Schema::table('externals', function (Blueprint $table) {
            $table->string('cin')->nullable()->after('email');
            $table->string('country')->default('Maroc')->after('cin')->comment('Pays d\'origine');
            $table->text('address')->nullable()->after('country');
            $table->string('function')->nullable()->after('address')->comment('Fonction');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('externals', function (Blueprint $table) {
            $table->dropColumn(['cin', 'country', 'address', 'function']);
        });
    }
};
