<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('beneficiaires', function (Blueprint $table) {
            // Remove old simple validation
            $table->dropColumn('is_validated');
            
            // Add simple hierarchical validation
            $table->string('validation_status')->default('pending')->after('created_by');
            // Values: 'pending', 'validation_1', 'validation_2', 'rejected'
            
            $table->foreignId('validated_by_1')
                ->nullable()
                ->after('validation_status')
                ->constrained('users')
                ->onDelete('set null');
            $table->timestamp('validated_at_1')->nullable()->after('validated_by_1');
            
            $table->foreignId('validated_by_2')
                ->nullable()
                ->after('validated_at_1')
                ->constrained('users')
                ->onDelete('set null');
            $table->timestamp('validated_at_2')->nullable()->after('validated_by_2');
        });
    }

    public function down(): void
    {
        Schema::table('beneficiaires', function (Blueprint $table) {
            $table->dropForeign(['validated_by_1']);
            $table->dropForeign(['validated_by_2']);
            
            $table->dropColumn([
                'validation_status',
                'validated_by_1',
                'validated_at_1',
                'validated_by_2',
                'validated_at_2',
            ]);
            
            $table->boolean('is_validated')->default(false)->after('created_by');
        });
    }
};
