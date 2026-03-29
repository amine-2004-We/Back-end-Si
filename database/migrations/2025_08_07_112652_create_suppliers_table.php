<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('supplier_id')->unique();
            $table->string('company_name');
            $table->string('trade_name')->nullable();
            $table->string('supplier_type');
            $table->string('business_sector');
            $table->string('address');
            $table->string('city');
            $table->string('country');
            $table->string('phone');
            $table->string('email');
            $table->string('contact_person')->nullable();
            $table->string('contact_person_role')->nullable();
            $table->string('legal_status');
            $table->string('tax_id')->unique();
            $table->string('commercial_register_number')->nullable();
            $table->string('rib')->nullable();
            $table->json('supporting_documents')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

          DB::statement('CREATE UNIQUE INDEX suppliers_email_unique ON suppliers (email) WHERE deleted_at IS NULL');

        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {    DB::statement('DROP INDEX IF EXISTS suppliers_email_unique');
        Schema::dropIfExists('suppliers');


    }
};