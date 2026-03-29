<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_name');
            $table->string('project_code')->unique();
            $table->enum('project_nature', ['Opérationnel', 'Institutionnel', 'Expérimental']);
            $table->unsignedBigInteger('project_type_id');
            $table->unsignedBigInteger('project_status_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->date('actual_start_date')->nullable();
            $table->unsignedBigInteger('responsible_id');
            $table->decimal('total_budget', 15, 2);
            $table->unsignedBigInteger('project_bank_account_id');
            $table->decimal('zakoura_contribution', 15, 2);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by_id');

            $table->foreign('created_by_id')->references('id')->on('users');
            $table->foreign('project_bank_account_id')->references('id')->on('project_bank_accounts');
            $table->foreign('responsible_id')->references('id')->on('users');
            $table->foreign('project_type_id')->references('id')->on('project_types');
            $table->foreign('project_status_id')->references('id')->on('project_statuses');

            $table->softDeletes();
            $table->timestamps();
        });

        DB::statement('CREATE UNIQUE INDEX projects_project_name_unique ON projects (project_name) WHERE deleted_at IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS projects_project_name_unique');
        Schema::dropIfExists('projects');
    }
};
