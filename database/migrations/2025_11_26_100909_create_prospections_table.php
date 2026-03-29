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
        Schema::create('prospections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prospections_id')->unique(); // UUID métier
            // Foreign keys
            $table->foreignId('program_id')->constrained('programs');
            $table->foreignId('site_id')->constrained('sites');
            // Identification
            $table->string('title');
            //zone
            $table->enum('douar_access', [
                'route_goudronnee',
                'accessible_en_voiture',
                'accessible_uniquement_en_4x4',
                'accessible_en_moto',
                'accessible_uniquement_a_pied',
            ]);
            $table->float('distance_to_nearest_douar_km')->nullable();
            $table->float('distance_to_nearest_city_km')->nullable();
            $table->enum('main_language', [
                'arabe_darija',
                'amazigh',
                'arabe_classique',
                'francais',
                'anglais',
            ]);
            //caracteristique de la population
            $table->integer('households')->nullable();
            $table->integer('total_population')->nullable();
            $table->float('literacy_rate')->nullable();
            $table->float('schooling_rate')->nullable();
            //enfants 0-5, 6-12, 13-18 b-boys g-girls
            $table->integer('children_0_5_b')->nullable();
            $table->integer('children_0_5_g')->nullable();
            $table->integer('children_6_12_b')->nullable();
            $table->integer('children_6_12_g')->nullable();
            $table->integer('children_13_18_b')->nullable();
            $table->integer('children_13_18_g')->nullable();
            // jeunes 18-35
            $table->integer('youth_m')->nullable();
            $table->integer('youth_f')->nullable();
            // Adultes > 35
            $table->integer('adults_m')->nullable();
            $table->integer('adults_f')->nullable();
            //acces education,sante,service sociaux et economique
            $table->boolean('preschool')->default(false);//prescolaire
            $table->integer('preschool_count')->nullable();

            $table->boolean('primary_school')->default(false);
            $table->integer('primary_school_count')->nullable();

            $table->boolean('middle_school')->default(false); // collège
            $table->integer('middle_school_count')->nullable();

            $table->boolean('high_school')->default(false); // lycée
            $table->integer('high_school_count')->nullable();

            $table->boolean('second_chance_center')->default(false);
            $table->integer('second_chance_center_count')->nullable();

            $table->boolean('health_center')->default(false);
            $table->integer('health_center_count')->nullable();

            $table->boolean('pharmacy')->default(false);
            $table->integer('pharmacy_count')->nullable();

            $table->boolean('training_center')->default(false); // qualification pro
            $table->integer('training_center_count')->nullable();

            $table->boolean('literacy_center')->default(false);//centre alphabetisation
            $table->integer('literacy_center_count')->nullable();

            $table->boolean('guidance_center')->default(false); // orientation scolaire
            $table->integer('guidance_center_count')->nullable();

            $table->boolean('youth_house')->default(false);
            $table->integer('youth_house_count')->nullable();

            $table->boolean('active_associations')->default(false);
            $table->text('association_names')->nullable(); // JSON or text list
            $table->enum('association_activity_type', [
                'aide_humanitaire_et_assistance',
                'insertion_sociale_et_professionnelle',
                'service_a_la_personne',
                'education_et_formation',
                'activites_socioculturelles_et_sportives',
            ]); // dropdown
            $table->integer('association_members_count')->nullable();
            $table->string('association_contact')->nullable();

            $table->boolean('educational_clubs')->default(false);
            $table->integer('educational_clubs_count')->nullable();

            $table->boolean('sports_space')->default(false);
            $table->integer('sports_space_count')->nullable();

            $table->boolean('school_transport')->default(false);
            $table->string('transport_provider')->nullable();

            $table->boolean('student_accommodation')->default(false);
            $table->integer('student_accommodation_count')->nullable();

            $table->text('economic_activities')->nullable();
            $table->text('cultural_activities')->nullable();
            //local prospecte
          

            $table->string('local_name')->nullable();
            $table->enum('owner_type', [
                'public',
                'prive',
                'associatif',
                'physique',
                'autre'
            ])->nullable();


            $table->enum('owner_status', [
                'indh',
                'aref',
                'entraide_national',
                'maison_des_jeunes',
                'commune',
                'association',
                'particulier',
                'autre',
            ])->nullable();


            $table->enum('manager_type', [
                'public',
                'prive',
                'associatif',
                'physique',
                'autre'
            ])->nullable();


            $table->enum('manager_structure', [
                'indh',
                'aref',
                'entraide_national',
                'maison_des_jeunes',
                'commune',
                'association',
                'particulier',
                'autre',
            ])->nullable();


            $table->integer('surface_area')->nullable();             
            $table->integer('available_rooms_count')->nullable();    
            $table->integer('training_rooms_count')->nullable();     
            $table->integer('management_rooms_count')->nullable();   
            $table->integer('sanitary_blocks_count')->nullable();    


            $table->boolean('water_connection')->default(false);
            $table->boolean('electricity_connection')->default(false);
            $table->boolean('internet_access')->default(false);


            $table->boolean('security')->default(false);


            $table->boolean('needs_renovation')->default(false);//amenagements
            $table->text('renovation_type')->nullable();
            //equipement
            $table->boolean('has_equipment')->default(false);

            $table->integer('individual_tables_count')->nullable();
            $table->enum('individual_tables_condition', ['good', 'bad'])->nullable();

            $table->integer('collective_tables_count')->nullable();
            $table->enum('collective_tables_condition', ['good', 'bad'])->nullable();

            $table->integer('boards_count')->nullable();
            $table->enum('boards_condition', ['good', 'bad'])->nullable();

            $table->integer('display_boards_count')->nullable();
            $table->enum('display_boards_condition', ['good', 'bad'])->nullable();

            $table->integer('coat_hangers_count')->nullable();
            $table->enum('coat_hangers_condition', ['good', 'bad'])->nullable();

            $table->integer('teacher_desks_count')->nullable();
            $table->enum('teacher_desks_condition', ['good', 'bad'])->nullable();

            $table->integer('teacher_chairs_count')->nullable();
            $table->enum('teacher_chairs_condition', ['good', 'bad'])->nullable();

            $table->integer('children_chairs_count')->nullable();
            $table->enum('children_chairs_condition', ['good', 'bad'])->nullable();

            $table->integer('student_chairs_count')->nullable();
            $table->enum('student_chairs_condition', ['good', 'bad'])->nullable();

            $table->integer('cabinets_count')->nullable();
            $table->enum('cabinets_condition', ['good', 'bad'])->nullable();

            $table->integer('storage_wardrobes_count')->nullable();
            $table->enum('storage_wardrobes_condition', ['good', 'bad'])->nullable();

            $table->integer('libraries_count')->nullable();
            $table->enum('libraries_condition', ['good', 'bad'])->nullable();

            $table->integer('extinguishers_count')->nullable();
            $table->enum('extinguishers_condition', ['good', 'bad'])->nullable();

            $table->integer('desktop_computers_count')->nullable();
            $table->enum('desktop_computers_condition', ['good', 'bad'])->nullable();

            $table->integer('laptops_count')->nullable();
            $table->enum('laptops_condition', ['good', 'bad'])->nullable();

            $table->integer('printers_count')->nullable();
            $table->enum('printers_condition', ['good', 'bad'])->nullable();

            $table->integer('copiers_count')->nullable();
            $table->enum('copiers_condition', ['good', 'bad'])->nullable();

            $table->integer('video_projectors_count')->nullable();
            $table->enum('video_projectors_condition', ['good', 'bad'])->nullable();

            $table->integer('projection_screens_count')->nullable();
            $table->enum('projection_screens_condition', ['good', 'bad'])->nullable();

            $table->integer('interactive_boards_count')->nullable();
            $table->enum('interactive_boards_condition', ['good', 'bad'])->nullable();

            // "Other" equipment
            $table->integer('other_equipment_count')->nullable();
            $table->enum('other_equipment_condition', ['good', 'bad'])->nullable();
            $table->text('other_equipment_description')->nullable();

            // Outdoor spaces
            $table->boolean('has_outdoor_spaces')->default(false);

            // Other local to prospect
            $table->text('other_local_to_prospect')->nullable();

            // Observations
            $table->text('equipment_observations')->nullable();


            // Decision (enum)
            $table->enum('decision', [
                'favorable',
                'to_be_investigated',
                'not_favorable'
            ])->nullable();

            // Potential projects (multiple choices → stored as JSON)
            $table->json('potential_projects')->nullable();

            // Observations
            $table->text('final_observations')->nullable();
            
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prospections');
    }
};
