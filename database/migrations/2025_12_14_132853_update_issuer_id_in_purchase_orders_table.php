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
    Schema::table('purchase_orders', function (Blueprint $table) {
        $table->dropForeign(['issuer_id']);
    });

    Schema::table('purchase_orders', function (Blueprint $table) {
        $table->foreign('issuer_id')
              ->references('id')
              ->on('users')
              ->restrictOnDelete();
    });
}

public function down(): void
{
    Schema::table('purchase_orders', function (Blueprint $table) {
        $table->dropForeign(['issuer_id']);
    });

    Schema::table('purchase_orders', function (Blueprint $table) {
        $table->foreign('issuer_id')
              ->references('id')
              ->on('collaborators')
              ->restrictOnDelete();
    });
}

};
