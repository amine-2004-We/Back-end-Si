<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            //NaturePartnerSeeder::class,
            //StatusPartnerSeeder::class,
            //StructurePartnerSeeder::class,
            CountrySeeder::class,
            TypeDepartementSeeder::class,
            DepartementSeeder::class,
            PositionSeeder::class,
            PhaseSeeder::class,
            PlanTypeSeeder::class,
            EducationalProgramSeeder::class,
            InterventionAxeSeeder::class,
            PreschoolPedagogiqueProgramSeeder::class,
            ProgrammePedagogiqueKaderSeeder::class,
            PsProgrammesSeeder::class,
        ]);
    }
}
