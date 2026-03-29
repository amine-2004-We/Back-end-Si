<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('grants', function (Blueprint $table) {
            $table->id();
            $table->string('grant_id')->unique();
            $table->foreignId('partner_id')->constrained('partners'); 
            $table->foreignId('convention_id')->constrained('conventions');
            $table->foreignId('project_id')->constrained('projects'); 
            $table->foreignId('bank_account_id')->constrained('project_bank_accounts'); 
            $table->decimal('committed_amount', 15, 2);
            $table->decimal('received_amount', 15, 2)->default(0.00);
            $table->enum('currency', ['MAD', 'EUR', 'USD']);
            $table->date('agreement_date');
            $table->json('received_dates')->nullable(); 
            $table->enum('reception_method', ['Virement', 'Chèque', 'Cash'])->nullable();
            $table->text('intended_use')->nullable();
            $table->string('status')->default('En attente');
            $table->text('comments')->nullable();
              $table->string('proof_document_attachment_path')->nullable();
              $table->string('payment_schedule_attachment_path')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreignId('created_by')->constrained('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('grants');
    }
};