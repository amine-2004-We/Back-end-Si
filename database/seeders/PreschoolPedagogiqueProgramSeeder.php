<?php

namespace Database\Seeders;

use App\Models\PreschoolPedagogiqueProgram;
use Illuminate\Database\Seeder;

class PreschoolPedagogiqueProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define the mapping of subcomponent IDs to Phase IDs (starting from 38)
        $phase_id_map = 38;

        /**
         * Helper function to extract and normalize Arabic and French/English names.
         * This logic is kept for consistency with the provided example seeder.
         */
        $parse_project_name = function (string $combined_name): array {
            $ar = null;
            $en = null;

            if (str_contains($combined_name, '/')) {
                $parts = explode('/', $combined_name, 2);
                $ar_part = trim($parts[0]);
                $en_part = trim($parts[1]);

                $ar = empty($ar_part) ? null : $ar_part;
                $en = empty($en_part) ? null : $en_part;

            } else {
                $trimmed_name = trim($combined_name);

                if (preg_match('/[ء-ي]/u', $trimmed_name)) {
                    $ar = $trimmed_name;
                    $en = null;
                } else {
                    $ar = null;
                    $en = $trimmed_name;
                }
                if (empty($trimmed_name)) {
                    $ar = null;
                    $en = null;
                }
            }
            return [
                'ar' => $ar,
                'en' => $en,
            ];
        };

        $programs = [
            [
                'pedagogical_project' => 'استئناس / Préparation et familiarisation',
                'phase_id'            => $phase_id_map++,
                'duration'            => 10,
            ],

            [
                'pedagogical_project' => 'أكتشف ذاتي / Je découvre mon identité',
                'phase_id'            => $phase_id_map++,
                'duration'            => 15,
            ],
            [
                'pedagogical_project' => 'أكتشف ذاتي / Je découvre mon identité',
                'phase_id'            => $phase_id_map++,
                'duration'            => 15,
            ],
            [
                'pedagogical_project' => 'أكتشف ذاتي / Je découvre mon identité',
                'phase_id'            => $phase_id_map++,
                'duration'            => 15,
            ],
            [
                'pedagogical_project' => 'أكتشف ذاتي / Je découvre mon identité',
                'phase_id'            => $phase_id_map++,
                'duration'            => 5,
            ],

            [
                'pedagogical_project' => 'في مدرستي / À l’école',
                'phase_id'            => $phase_id_map++,
                'duration'            => 15,
            ],
            [
                'pedagogical_project' => 'في مدرستي / À l’école',
                'phase_id'            => $phase_id_map++,
                'duration'            => 15,
            ],
            [
                'pedagogical_project' => 'في مدرستي / À l’école',
                'phase_id'            => $phase_id_map++,
                'duration'            => 15,
            ],
            [
                'pedagogical_project' => 'في مدرستي / À l’école',
                'phase_id'            => $phase_id_map++,
                'duration'            => 5,
            ],

            [
                'pedagogical_project' => 'أكتشف محيطي / Je découvre mon environnement',
                'phase_id'            => $phase_id_map++,
                'duration'            => 15,
            ],
            [
                'pedagogical_project' => 'أكتشف محيطي / Je découvre mon environnement',
                'phase_id'            => $phase_id_map++,
                'duration'            => 15,
            ],
            [
                'pedagogical_project' => 'أكتشف محيطي / Je découvre mon environnement',
                'phase_id'            => $phase_id_map++,
                'duration'            => 15,
            ],
            [
                'pedagogical_project' => 'أكتشف محيطي / Je découvre mon environnement',
                'phase_id'            => $phase_id_map++,
                'duration'            => 5,
            ],
            [
                'pedagogical_project' => 'حفل نهاية السنة / Fête de fin d’année',
                'phase_id'            => $phase_id_map++,
                'duration'            => 10,
            ],
        ];

        foreach ($programs as $data) {
            $project_names = $parse_project_name($data['pedagogical_project']);
            $project_name_to_store = $project_names['en'];
            $project_name_arabe_to_store = $project_names['ar'];

            PreschoolPedagogiqueProgram::updateOrCreate(
                [
                    'subcomponent' => $data['phase_id'],
                ],
                [
                    'pedagogical_project' => $project_name_to_store,
                    'pedagogical_project_arabe' => $project_name_arabe_to_store,
                    'subcomponent' => $data['phase_id'],
                    'duration' => $data['duration'],
                ]
            );
        }
    }
}
