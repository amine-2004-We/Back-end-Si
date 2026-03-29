    <?php

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
            Schema::create('communes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('cercle_id')->nullable()->constrained('cercles')->onDelete('set null');
                $table->string('name'); 
                $table->string('code'); 
                $table->timestamps();

                $table->unique(['code', 'cercle_id']); 
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::dropIfExists('communes');
        }
    };
    