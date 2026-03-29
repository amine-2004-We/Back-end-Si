<?php

use App\Enums\FinalAcceptanceStatusEnum;
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
        Schema::create('final_acceptances', function (Blueprint $table) {
            $table->id();
            $table->string('identifier')->unique();

             $table->foreignId('provisional_acceptance_id')->constrained('provisional_acceptances');

             $table->foreignId('calltender_id')->constrained('calltenders');

            $table->date('final_acceptance_date');

            $table->text('observations')->nullable();

             $table->string('status')->default(FinalAcceptanceStatusEnum::VALIDATED->value);

             $table->boolean('auto_close_contract')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('final_acceptances');
    }
};
