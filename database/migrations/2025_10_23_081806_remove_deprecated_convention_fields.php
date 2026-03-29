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
        Schema::table('conventions', function (Blueprint $table) {
            $table->dropForeign(['responsible_id']);
            $table->dropColumn(['responsible_id', 'observations']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conventions', function (Blueprint $table) {
            $table->foreignId('responsible_id')->nullable()->constrained('collaborators')->after('status');
            $table->text('observations')->nullable()->after('amount');
        });
    }
};
