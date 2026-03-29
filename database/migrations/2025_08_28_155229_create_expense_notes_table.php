<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_notes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();
            $table->morphs('beneficiary');
            $table->foreignId('training_id')->constrained('trainings')->cascadeOnDelete();
            $table->date('expense_date');
            $table->enum('expense_nature', ['transport', 'lodging', 'meals', 'misc']);
            $table->decimal('amount_ttc', 14, 2);
            $table->string('attachment_path')->nullable();
            $table->foreignId('budget_line_id')->constrained('budget_lines');
            $table->enum('validation_status', ['draft', 'in_review', 'approved', 'rejected'])->default('draft');
            $table->text('comments')->nullable();
            $table->foreignId('created_by_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['validation_status', 'expense_date']);
        });

        try {
            DB::statement("ALTER TABLE expense_notes ADD CONSTRAINT expense_notes_amount_nonneg CHECK (amount_ttc >= 0)");
        } catch (\Throwable $e) {}
    }

    public function down(): void
    {
        try { DB::statement("ALTER TABLE expense_notes DROP CONSTRAINT IF EXISTS expense_notes_amount_nonneg"); } catch (\Throwable $e) {}
        Schema::dropIfExists('expense_notes');
    }
};
