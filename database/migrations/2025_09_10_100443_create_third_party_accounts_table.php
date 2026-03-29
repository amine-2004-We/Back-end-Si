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
        Schema::create('third_party_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');

            $table->foreignId('general_account_id')
                  ->constrained('general_accounts')
                  ->cascadeOnDelete();

            $table->string('subdivision', 4);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['general_account_id', 'subdivision']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('third_party_accounts');
    }
};
