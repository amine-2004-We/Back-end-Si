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
        //
    Schema::create('beneficiary_status_history', function (Blueprint $table) {
        $table->id();
        $table->foreignId('beneficiary_id')->constrained('beneficiaires');
        $table->string('status');
        $table->dateTime('change_date');
        $table->text('reason');
        $table->foreignId('created_by')->constrained('users');
        $table->foreignId('previous_group_id')->nullable()->constrained('groups')->onDelete('set null');
        $table->foreignId('transfer_commune_id')->nullable()->constrained('communes')->onDelete('set null');
        $table->foreignId('transfer_class_id')->nullable()->constrained('class')->onDelete('set null');
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('beneficiary_status_history');
    }
};
