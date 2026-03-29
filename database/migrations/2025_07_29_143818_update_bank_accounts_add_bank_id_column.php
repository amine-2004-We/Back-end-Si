<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('project_bank_accounts', function (Blueprint $table) {
            $table->dropColumn(['bic_swift', 'bank','currency']);

            $table->foreignId('bank_id')
                ->constrained('banks')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {

    }
};
