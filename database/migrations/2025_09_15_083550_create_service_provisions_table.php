<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\ServiceProvisionTypeEnum;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('service_provisions', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('budget_line_id')->constrained('budget_lines')->onDelete('restrict');
            $table->date('provision_date');
            $table->decimal('amount', 15, 2);
            $table->string('supplier');
            $table->text('description');
            $table->string('justification_path');
            $table->string('type')->default(ServiceProvisionTypeEnum::OTHER->value);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_provisions');
    }
};