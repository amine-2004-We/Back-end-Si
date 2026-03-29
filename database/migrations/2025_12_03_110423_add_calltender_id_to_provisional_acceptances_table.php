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
        Schema::table('provisional_acceptances', function (Blueprint $table) {
            $table->foreignId('calltender_id')->nullable()->constrained('calltenders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('provisional_acceptances', function (Blueprint $table) {
            $table->dropForeign(['calltender_id']);
            $table->dropColumn('calltender_id');
        });
    }
};
