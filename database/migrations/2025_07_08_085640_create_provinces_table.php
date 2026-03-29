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
            Schema::create('provinces', function (Blueprint $table) {
                $table->id();
                $table->foreignId('region_id')->constrained('regions')->onDelete('cascade');
                $table->string('name'); 
                $table->string('code'); 
                $table->timestamps();

                $table->unique(['code', 'region_id']); 
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::dropIfExists('provinces');
        }
    };
    