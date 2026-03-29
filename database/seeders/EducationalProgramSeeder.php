<?php

namespace Database\Seeders;

use App\Models\EducationalProgram;
use App\Models\User;
use Illuminate\Database\Seeder;

class EducationalProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->info('No users found, skipping EducationalProgram seeding.');
            return;
        }

        /**
         * Helper function to extract and normalize Arabic and French/English names.
         * Returns null if a component is empty or not present.
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
            // --- Level 1
            [
                'pedagogical_project' => 'أسبوع الاستئناس / Préparation et familiarisation',
                'subcomponent'        => 6,
                'duration'            => 10,
                'level'               => 1,
            ],
            [
                'pedagogical_project' => 'أنا وأسرتي / Ma petite famille et moi',
                'subcomponent'        => 7,
                'duration'            => 10,
                'level'               => 1,
            ],
            [
                'pedagogical_project' => 'أنا وأسرتي / Ma petite famille وmoi',
                'subcomponent'        => 8,
                'duration'            => 10,
                'level'               => 1,
            ],
            [
                'pedagogical_project' => 'أنا وأسرتي / Ma petite famille et moi',
                'subcomponent'        => 9,
                'duration'            => 10,
                'level'               => 1,
            ],
            [
                'pedagogical_project' => 'أنا ومحيطي / Mon environnement et moi',
                'subcomponent'        => 10,
                'duration'            => 10,
                'level'               => 1,
            ],
            [
                'pedagogical_project' => 'أنا ومحيطي / Mon environnement et moi',
                'subcomponent'        => 11,
                'duration'            => 10,
                'level'               => 1,
            ],
            [
                'pedagogical_project' => 'أنا ومحيطي / Mon environnement et moi',
                'subcomponent'        => 12,
                'duration'            => 10,
                'level'               => 1,
            ],
            [
                'pedagogical_project' => 'أسبو ع التتبع والدعم / Suivi et soutien',
                'subcomponent'        => 13,
                'duration'            => 5,
                'level'               => 1,
            ],
            [
                'pedagogical_project' => 'قريتي / Ma campagne',
                'subcomponent'        => 14,
                'duration'            => 10,
                'level'               => 1,
            ],
            [
                'pedagogical_project' => 'قريتي / Ma campagne',
                'subcomponent'        => 15,
                'duration'            => 10,
                'level'               => 1,
            ],
            [
                'pedagogical_project' => 'قريتي / Ma campagne',
                'subcomponent'        => 16,
                'duration'            => 10,
                'level'               => 1,
            ],
            [
                'pedagogical_project' => 'أنا والطبيعة / La nature et moi',
                'subcomponent'        => 17,
                'duration'            => 10,
                'level'               => 1,
            ],
            [
                'pedagogical_project' => 'أنا والطبيعة / La nature et moi',
                'subcomponent'        => 18,
                'duration'            => 10,
                'level'               => 1,
            ],
            [
                'pedagogical_project' => 'أنا والطبيعة / La nature et moi',
                'subcomponent'        => 19,
                'duration'            => 10,
                'level'               => 1,
            ],
            [
                'pedagogical_project' => 'أسبو ع التتبع والدعم / Suivi et soutien',
                'subcomponent'        => 13,
                'duration'            => 5,
                'level'               => 1,
            ],
            [
                'pedagogical_project' => 'عادات وتقاليد منطقتي / Je découvre les traditions et coutumes de ma région',
                'subcomponent'        => 20,
                'duration'            => 10,
                'level'               => 1,
            ],
            [
                'pedagogical_project' => 'عادات وتقاليد منطقتي / Je découvre les traditions et coutumes de ma région',
                'subcomponent'        => 21,
                'duration'            => 10,
                'level'               => 1,
            ],
            [
                'pedagogical_project' => 'عادات وتقاليد منطقتي / Je découvre les traditions et coutumes de ma région',
                'subcomponent'        => 22,
                'duration'            => 10,
                'level'               => 1,
            ],

            // Level 2
            [
                'pedagogical_project' => 'أسبوع الاستئناس / Préparation et familiarisation',
                'subcomponent'        => 6,
                'duration'            => 10,
                'level'               => 2,
            ],
            [
                'pedagogical_project' => 'أنا وعائلتي / Ma grande famille et moi',
                'subcomponent'        => 23,
                'duration'            => 10,
                'level'               => 2,
            ],
            [
                'pedagogical_project' => 'أنا وعائلتي / Ma grande famille et moi',
                'subcomponent'        => 24,
                'duration'            => 10,
                'level'               => 2,
            ],
            [
                'pedagogical_project' => 'أنا وعائلتي / Ma grande famille et moi',
                'subcomponent'        => 25,
                'duration'            => 10,
                'level'               => 2,
            ],
            [
                'pedagogical_project' => 'أتواصل في محيطي / Je communique avec mon environnement',
                'subcomponent'        => 26,
                'duration'            => 10,
                'level'               => 2,
            ],
            [
                'pedagogical_project' => 'أتواصل في محيطي / Je communique avec mon environnement',
                'subcomponent'        => 27,
                'duration'            => 10,
                'level'               => 2,
            ],
            [
                'pedagogical_project' => 'أتواصل في محيطي / Je communique avec mon environnement',
                'subcomponent'        => 28,
                'duration'            => 10,
                'level'               => 2,
            ],
            [
                'pedagogical_project' => 'أسبو ع التتبع والدعم / Suivi et soutien',
                'subcomponent'        => 13,
                'duration'            => 5,
                'level'               => 2,
            ],
            [
                'pedagogical_project' => 'مدينتي / Ma ville',
                'subcomponent'        => 29,
                'duration'            => 10,
                'level'               => 2,
            ],
            [
                'pedagogical_project' => 'مدينتي / Ma ville',
                'subcomponent'        => 30,
                'duration'            => 10,
                'level'               => 2,
            ],
            [
                'pedagogical_project' => 'مدينتي / Ma ville',
                'subcomponent'        => 31,
                'duration'            => 10,
                'level'               => 2,
            ],
            [
                'pedagogical_project' => 'أحافظ على بيئتي / La nature et Moi',
                'subcomponent'        => 32,
                'duration'            => 10,
                'level'               => 2,
            ],
            [
                'pedagogical_project' => 'أحافظ على بيئتي / La nature et Moi',
                'subcomponent'        => 33,
                'duration'            => 10,
                'level'               => 2,
            ],
            [
                'pedagogical_project' => 'أحافظ على بيئتي / La nature et Moi',
                'subcomponent'        => 34,
                'duration'            => 10,
                'level'               => 2,
            ],
            [
                'pedagogical_project' => 'أسبو ع التتبع والدعم / Suivi et soutien',
                'subcomponent'        => 13,
                'duration'            => 5,
                'level'               => 2,
            ],
            [
                'pedagogical_project' => 'أكتشف عادات وتقاليد بلدي',
                'subcomponent'        => 35,
                'duration'            => 10,
                'level'               => 2,
            ],
            [
                'pedagogical_project' => 'أكتشف عادات وتقاليد بلدي',
                'subcomponent'        => 36,
                'duration'            => 10,
                'level'               => 2,
            ],
            [
                'pedagogical_project' => 'أكتشف عادات وتقاليد بلدي',
                'subcomponent'        => 37,
                'duration'            => 10,
                'level'               => 2,
            ],
        ];

        foreach ($programs as $data) {
            $names = $parse_project_name($data['pedagogical_project']);
            $project_name_to_store = $names['en'];
            $project_name_arabe_to_store = $names['ar'];


            EducationalProgram::updateOrCreate(
                [
                    'level'=> $data['level'],
                    'subcomponent'=> $data['subcomponent'],
                    'pedagogical_project'=> $data['pedagogical_project'],
                ],
                [
                    'pedagogical_project'=> $project_name_to_store,
                    'pedagogical_project_arabe' => $project_name_arabe_to_store,
                    'duration'=> $data['duration'],
                ]
            );
        }
    }
}
