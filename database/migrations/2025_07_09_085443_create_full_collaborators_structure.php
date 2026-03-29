<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create collaborator_status table
        Schema::create('collaborator_status', function (Blueprint $table) {
            $table->id();
            $table->string('type')->unique();
            $table->softDeletes();
            $table->timestamps();
        });

        // Create contract_types table
        Schema::create('contract_types', function (Blueprint $table) {
            $table->id();
            $table->string('type')->unique();
            $table->softDeletes();
            $table->timestamps();
        });

        // Create contract_status table
        Schema::create('contract_status', function (Blueprint $table) {
            $table->id();
            $table->string('status')->unique();
            $table->softDeletes();
            $table->timestamps();
        });

        // Create collaborators table
        Schema::create('collaborators', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('last_name');
            $table->string('first_name');
            $table->string('last_name_ar')->nullable();
            $table->string('first_name_ar')->nullable();
            $table->string('phone');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('cin');
            $table->string('cnss')->nullable();
            $table->string('cimr')->nullable();
            $table->string('insurance_membership_number')->nullable();
            $table->string('bank');
            $table->string('rib');
            $table->date('birth_date');
            $table->string('birth_region');
            $table->string('birth_province');
            $table->text('residence_address');
            $table->string('residence_region');
            $table->string('residence_province');
            $table->string('organizational_unit');
            $table->string('position');
            $table->string('source');
            $table->date('entry_date')->nullable();
            $table->date('exit_date')->nullable();
            $table->decimal('gross_salary', 10, 2);
            $table->decimal('net_salary', 10, 2);
            $table->integer('trial_period');
            $table->integer('notice_period');
            $table->integer('total_experience')->nullable();
            $table->integer('educational_experience')->nullable();
            $table->decimal('bonuses', 10, 2)->nullable();
            $table->integer('annual_leave_days');
            $table->string('assigned_project');
            $table->string('assigned_region');
            $table->string('assigned_province');
            $table->string('marital_status');
            $table->string('education_level')->nullable();
            $table->string('discipline')->nullable();
            $table->string('institution')->nullable();
            $table->date('graduation_date')->nullable();
            $table->string('family_member')->nullable();
            $table->string('family_relationship')->nullable();
            $table->string('photo')->nullable();
            $table->foreignId('hierarchical_superior')->nullable()->constrained('collaborators')->nullOnDelete();
            $table->foreignId('collaborator_status_id')->constrained('collaborator_status')->restrictOnDelete();
            $table->foreignId('contract_type_id')->constrained('contract_types')->restrictOnDelete();
            $table->foreignId('contract_status_id')->constrained('contract_status')->restrictOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collaborators');
        Schema::dropIfExists('contract_status');
        Schema::dropIfExists('contract_types');
        Schema::dropIfExists('collaborator_status');
    }
};
