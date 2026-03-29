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
        Schema::create('mission_orders', function (Blueprint $table) {
            $table->id();
            $table->string('mission_order_code');
            $table->text('mission_description');
            $table->text('objective')->nullable();
            $table->foreignId('project_id')->constrained();
            $table->foreignId('collaborator_id')->constrained('collaborators');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('mission_type', ['internal', 'external']);
            $table->enum('status', ['pending', 'approved', 'refused'])->default('pending');
            $table->boolean('is_advance_requested');
            $table->foreignId('province_id')->constrained('provinces');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mission_orders');
    }
};
