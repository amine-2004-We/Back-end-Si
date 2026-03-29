<?php

use App\Enums\ProvisionalAcceptanceStatusEnum;
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
        Schema::create('provisional_acceptances', function (Blueprint $table) {
            $table->id();
            $table->string('identifier')->unique();

            $table->foreignId('delivery_receipt_id')->constrained('delivery_receipts');

            // $table->foreignId('contract_id')->nullable()->constrained('calltenders');

            $table->date('provisional_acceptance_date');

            $table->text('reserves')->nullable();

            $table->text('corrective_actions')->nullable();

            $table->string('status')->default(ProvisionalAcceptanceStatusEnum::PENDING_RESERVES->value);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provisional_acceptances');
    }
};
