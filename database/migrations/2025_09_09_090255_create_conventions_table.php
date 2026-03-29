<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\ConventionType;
use App\Enums\ConventionStatus;
use App\Enums\CurrencyEnum;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('conventions', function (Blueprint $table) {
            $table->id();

            // Champs principaux
            $table->string('agreement_code')->unique();
            $table->string('title')->unique();
            $table->foreignId('partner_id')->constrained('partners');
            $table->enum('type', array_column(ConventionType::cases(), 'value'));
            $table->date('signed_at');
            $table->integer('duration_months');
            $table->date('estimated_end_date');
            $table->foreignId('responsible_id')
                ->constrained('collaborators')
                ->onDelete('cascade');
            $table->enum('status', array_column(ConventionStatus::cases(), 'value'));

            // Colonnes additionnelles
            $table->string('signed_document')->comment('Fichier PDF signé ou scanné, max 10 Mo'); // obligatoire
            $table->decimal('amount', 15, 2)->comment('Montant global engagé, en dirhams ou euros'); // obligatoire
            $table->text('observations')->nullable()->comment('Notes complémentaires ou clauses spécifiques');
            $table->unsignedBigInteger('created_by')->nullable()->comment('ID de l’utilisateur qui a créé la convention');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->enum('devise', CurrencyEnum::values())->comment('Devise de la convention'); // obligatoire

            // Timestamps et soft deletes
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conventions');
    }
};