<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['issuer_id']);
        });

        DB::statement('ALTER TABLE purchase_orders ALTER COLUMN issuer_id DROP NOT NULL');

        DB::statement('
            UPDATE purchase_orders po
            SET issuer_id = NULL
            WHERE issuer_id IS NOT NULL
            AND NOT EXISTS (
                SELECT 1 FROM collaborators c WHERE c.id = po.issuer_id
            )
        ');

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->foreign('issuer_id')
                  ->references('id')
                  ->on('collaborators')
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
                  ->on('users')
                  ->restrictOnDelete();
        });
    }
};
