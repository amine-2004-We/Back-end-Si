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
        Schema::create('budget_lines', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->unsignedBigInteger('budget_category_id');
            $table->unsignedBigInteger('project_id');
            $table->double('total_amount');
            $table->double('consumed_amount');
            $table->double('remaining_amount');
            $table->enum('status', ['active','consumed','on_alert']);

            $table->foreign('budget_category_id')->references('id')->on('budget_categories')->onDelete('restrict');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('restrict');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_lines');
    }
};
