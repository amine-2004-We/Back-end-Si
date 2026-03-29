<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('delivery_request_id')->nullable()->after('id');
            $table->foreign('delivery_request_id')->references('id')->on('delivery_request')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->dropForeign(['delivery_request_id']);
            $table->dropColumn('delivery_request_id');
        });
    }
};
