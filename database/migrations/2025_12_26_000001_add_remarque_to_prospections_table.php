<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prospections', function (Blueprint $table) {
            $table->text('remarque')->nullable()->after('adults_f');
        });
    }

    public function down(): void
    {
        Schema::table('prospections', function (Blueprint $table) {
            $table->dropColumn('remarque');
        });
    }
};