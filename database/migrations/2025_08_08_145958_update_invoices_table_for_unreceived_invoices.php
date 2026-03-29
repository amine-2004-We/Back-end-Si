<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'receipt_number')) {
                $table->dropColumn('receipt_number');
            }

            $table->unsignedBigInteger('receipt_id')->nullable()->after('id');

            $table->date('due_date')->nullable()->after('notes');

            $table->date('invoice_date')->nullable()->change();
        });

        DB::statement("ALTER TABLE invoices DROP CONSTRAINT invoices_status_check");

        DB::statement("ALTER TABLE invoices ADD CONSTRAINT invoices_status_check
            CHECK (status IN (
                'Non parvenue',
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
            ))");
    }

    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'receipt_number')) {
                $table->string('receipt_number')->nullable()->after('id');
            }

            $table->dropColumn('receipt_id');
            $table->dropColumn('due_date');

            $table->date('invoice_date')->nullable(false)->change();
        });
        DB::statement("ALTER TABLE invoices DROP CONSTRAINT invoices_status_check");

        DB::statement("ALTER TABLE invoices ADD CONSTRAINT invoices_status_check
            CHECK (status IN (
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
            ))");
        }

};
