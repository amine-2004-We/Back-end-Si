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
        Schema::table('presence_sheets', function (Blueprint $table) {
            $table->timestamp('validated_at')->nullable()->after('created_by');
            $table->foreignId('validated_by')->nullable()->constrained('users')->after('validated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presence_sheets', function (Blueprint $table) {
            //
        });
    }
};
