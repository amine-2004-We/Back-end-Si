<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
     public function up()
    {
        Schema::table('programs', function (Blueprint $table) {
            

            $table->string('code')->nullable()->change();
         $table->unsignedBigInteger('project_id')->nullable()->change(); 
            $table->text('main_objective')->nullable()->change();
            $table->string('status')->nullable()->change();
            
            // Date fields
            $table->date('start_date')->nullable()->change();
            $table->date('end_date')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('programs', function (Blueprint $table) {
          
            $table->string('code')->nullable(false)->change();
            $table->foreignId('project_id')->nullable(false)->constrained('projects')->change();
            $table->text('main_objective')->nullable(false)->change();
            $table->string('status')->nullable(false)->change();
            
            $table->date('start_date')->nullable(false)->change();
            $table->date('end_date')->nullable(false)->change();
        });
    }
};
