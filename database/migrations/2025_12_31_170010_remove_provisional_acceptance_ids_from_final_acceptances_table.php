<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('final_acceptances', function (Blueprint $table) {
            $table->dropColumn('provisional_acceptance_ids');
        });
    }

    public function down(): void
    {
        Schema::table('final_acceptances', function (Blueprint $table) {
            $table->json('provisional_acceptance_ids')->nullable();
        });
    }
};
