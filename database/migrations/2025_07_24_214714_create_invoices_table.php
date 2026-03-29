<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number');
            $table->string('invoice_number');
            $table->date('invoice_date');
            $table->enum('status', [
                'En attente de validation',
                'Annulé',
                'En cours de traitement',
                'Payé',
                'Rejeté',
                'Comptabilisé',
                'Traité',
                'Validé (Trésorerie)',
                'Validé 1 (Comptabilité)',
                'Validé 2 (CG)'
            ]);

            $table->string('subject');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Unique index only on non-deleted rows (PostgreSQL-specific)
        DB::statement('CREATE UNIQUE INDEX receipt_number_unique ON invoices (receipt_number) WHERE deleted_at IS NULL');
        DB::statement('CREATE UNIQUE INDEX invoice_number_unique ON invoices (invoice_number) WHERE deleted_at IS NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP INDEX receipt_number_unique');
        DB::statement('DROP INDEX invoice_number_unique');
        Schema::dropIfExists('invoices');
    }
};
