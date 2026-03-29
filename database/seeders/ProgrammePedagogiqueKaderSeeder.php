<?php

namespace Database\Seeders;

use App\Models\ProgrammePedagogiqueKader;
use App\Models\ProjectClass;
use Illuminate\Database\Seeder;

class ProgrammePedagogiqueKaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            // Cycle 1: Moyenne Section (4-5 years)
            [
                'phase_id'            => 52,
                'project_pedagogique' => 'Introduction à la découverte de soi et au respect des autres',
                'project_pedagogique_arabe' => 'مدخل اكتشاف الذات واحترام الآخرين',
                'activities' => [
                    'Activity 1: Qui suis-je ?',
                    'Activity 2: Je suis unique !',
                    'Activity 3: La chaîne des souris',
                    'Activity 4: La fabrique à câline',
                ],
                'activities_arabe' => 'النشاط 1: من أنا؟ النشاط 2: أنا فريد! النشاط 3: سلسلة الفئران النشاط 4: مصنع المعانقة',
                'observation' => 'Moyenne Section - Unité 1-4',
            ],
            [
                'phase_id'            => 53,
                'project_pedagogique' => 'Introduction aux valeurs entrepreneuriales',
                'project_pedagogique_arabe' => 'مدخل القيم المقاولاتية',
                'activities' => [
                    'Activity 5: Les petites mains qui aident',
                    'Activity 6: La carte des amitiés',
                    'Activity 7: La puzzle collectif',
                    'Activity 8: Les petits aventuriers des bois',
                    'Activity 9: La mission des petits écologistes',
                ],
                'activities_arabe' => 'النشاط 5: الأيدي الصغيرة التي تساعد النشاط 6: خريطة الصداقات النشاط 7: اللغز الجماعي النشاط 8: مغامرون الغابة الصغار النشاط 9: مهمة صغار البيئيين',
                'observation' => 'Moyenne Section - Unité 5-9',
            ],
            [
                'phase_id'            => 54,
                'project_pedagogique' => 'Introduction aux métiers et aux activités quotidiennes',
                'project_pedagogique_arabe' => 'مدخل القيم المقاولاتية',
                'activities' => [
                    'Activity 10: Le monde des métiers',
                    'Activity 11: L\'histoire de l\'entrepreneur',
                    'Activity 12: Le monde des idées',
                    'Activity 13: Le petit marché',
                ],
                'activities_arabe' => 'النشاط 10: عالم المهن النشاط 11: قصة رائد الأعمال النشاط 12: عالم الأفكار النشاط 13: السوق الصغير',
                'observation' => 'Moyenne Section - Unité 10-13',
            ],
            [
                'phase_id'            => 55,
                'project_pedagogique' => 'L\'art de créer et d\'imaginer',
                'project_pedagogique_arabe' => 'فن الإبداع والخيال',
                'activities' => [
                    'Activity 14: La boîte à merveilles',
                    'Activity 15: L\'arbre de créativité',
                    'Activity 16: Le chemin des petits penseurs',
                    'Activity 17: Les formes magiques',
                ],
                'activities_arabe' => 'النشاط 14: صندوق المعجزات النشاط 15: شجرة الإبداع النشاط 16: درب المفكرين الصغار النشاط 17: الأشكال السحرية',
                'observation' => 'Moyenne Section - Unité 14-17',
            ],
            [
                'phase_id'            => 56,
                'project_pedagogique' => 'Naissance du petit roi',
                'project_pedagogique_arabe' => 'ولادة الملك الأول',
                'activities' => [
                    'Activity 18: La grande parade des talents',
                ],
                'activities_arabe' => 'النشاط 18: الاحتفال بعبقرية الملك الأول',
                'observation' => 'Moyenne Section - Unité 18',
            ],

            // Cycle 2: Grande Section (5-6 years)
            [
                'phase_id'            => 57,
                'project_pedagogique' => 'Découverte de soi et de son potentiel entrepreneurial',
                'project_pedagogique_arabe' => 'اكتشاف الذات والقدرات المقاولاتية',
                'activities' => [
                    'Activité 1: Nos Super-pouvoirs',
                    'Activité 2: Théâtre des émotions',
                    'Activité 3: Le carnet des rêves',
                    'Activité 4: Le jardin des idées',
                    'Activité 5: Mon métier en dessin',
                    'Activité 6: Ma boîte à outils créative',
                    'Activité 7: Coloriage des petits entrepreneurs',
                    'Activité 8: Théâtre des petits entrepreneurs',
                ],
                'activities_arabe' => 'النشاط 1: قوانا الخارقة النشاط 2: مسرح العواطف النشاط 3: دفتر الأحلام النشاط 4: حديقة الأفكار النشاط 5: مهنتي برسم النشاط 6: صندوق أدواتي الإبداعية النشاط 7: تلوين روّاد الأعمال الصغار النشاط 8: مسرح روّاد الأعمال الصغار',
                'observation' => 'Grande Section - Unité 1-8',
            ],
            [
                'phase_id'            => 58,
                'project_pedagogique' => 'Je suis un petit entrepreneur',
                'project_pedagogique_arabe' => 'أنا رائد أعمال صغير',
                'activities' => [
                    'Activité 9: Je suis créatif',
                    'Activité 10: Je suis collaboratif',
                    'Activité 11: Je suis résolveur de problèmes',
                    'Activité 12: Je suis organisé',
                    'Activité 13: Je suis écologiste',
                    'Activité 14: Je suis responsable 1',
                    'Activité 15: Je suis responsable 2',
                    'Activité 16: Je célèbre ma culture',
                ],
                'activities_arabe' => 'النشاط 9: أنا مبدع النشاط 10: أنا متعاون النشاط 11: أنا حل المشاكل النشاط 12: أنا منظم النشاط 13: أنا بيئي النشاط 14: أنا مسؤول 1 النشاط 15: أنا مسؤول 2 النشاط 16: أحتفل بثقافتي',
                'observation' => 'Grande Section - Unité 9-16',
            ],
            [
                'phase_id'            => 59,
                'project_pedagogique' => 'Mini-projet en clôture du cycle',
                'project_pedagogique_arabe' => 'مشروع صغير و إغلاق الدورة',
                'activities' => [
                    'Activité 17: Mini-projet entrepreneurial',
                    'Activité 18: La fête des petits entrepreneurs',
                ],
                'activities_arabe' => 'النشاط 17: مشروع رائد أعمال صغير النشاط 18: حفل روّاد الأعمال الصغار',
                'observation' => 'Grande Section - Unité 17-18 (Clôture)',
            ],
        ];

        foreach ($data as $record) {
            ProgrammePedagogiqueKader::updateOrCreate(
                [
                    'subcomponent' => $record['phase_id'],
                ],
                [
                    'project_pedagogique' => $record['project_pedagogique'],
                    'project_pedagogique_arabe' => $record['project_pedagogique_arabe'],
                    'subcomponent' => $record['phase_id'],
                    'activities' => $record['activities'],
                    'activities_arabe' => $record['activities_arabe'],
                    'observation' => $record['observation'],
                ]
            );
        }

        $this->command->info('ProgrammePedagogiqueKader seeded successfully!');
    }
}