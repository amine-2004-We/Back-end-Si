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
        Schema::create('general_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->char('class', 1);
            $table->char('account', 1);
            $table->char('sub_account', 2);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['class', 'account', 'sub_account']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_accounts');
    }
};
