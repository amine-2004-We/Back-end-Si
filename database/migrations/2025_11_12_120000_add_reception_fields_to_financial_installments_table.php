<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        Schema::table('financial_installments', function (Blueprint $table) {
             $table->timestamp('reception_date')->nullable()->after('due_date');
             $table->decimal('amount_received', 15, 2)->nullable()->after('amount');
             $table->string('reception_mode', 100)->nullable()->after('amount_received');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('financial_installments', function (Blueprint $table) {
            $table->dropColumn(['reception_date', 'amount_received', 'reception_mode']);
        });
    }
};
