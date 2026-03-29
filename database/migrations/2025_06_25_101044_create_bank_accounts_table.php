<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('project_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('rib_iban');
            $table->string('account_title');
            $table->string('opening_country');
            $table->string('account_holder_name');
            $table->string('bic_swift');
            $table->date('opening_date')->nullable();
            $table->enum('currency', ['MAD', 'EUR', 'USD']);
            $table->enum('status', ['active', 'inactive', 'closed']);
            $table->text('supporting_document')->nullable();
            $table->text('comments')->nullable();
            $table->string('bank');
            $table->string('agency')->nullable();
            $table->unsignedBigInteger('created_by_id');

            $table->foreign('created_by_id')->references('id')->on('users');

            $table->softDeletes();
            $table->timestamps();
        });

        DB::statement("CREATE UNIQUE INDEX rib_iban_unique ON project_bank_accounts (rib_iban) WHERE deleted_at IS NULL");
        DB::statement("CREATE UNIQUE INDEX account_title_unique ON project_bank_accounts (account_title) WHERE deleted_at IS NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP INDEX IF EXISTS rib_iban_unique");
        DB::statement("DROP INDEX IF EXISTS account_title_unique");

        Schema::dropIfExists('project_bank_accounts');
    }
};
