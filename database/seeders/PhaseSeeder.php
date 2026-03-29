<?php

namespace Database\Seeders;

use App\Models\Phase;
use App\Models\User;
use Illuminate\Database\Seeder;

class PhaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->info('No users found, skipping Phase seeding.');
            return;
        }

        $phases_fr_en = [
            1 => 'Conception',
            2 => 'Préparation',
            3 => 'Concrétisation',
            4 => 'Opérationnalisation des process',
            5 => 'Déploiement opérationnel',
            6 => 'Domestication',
            7 => 'Je découvre mon corps',
            8 => 'Les membres de ma petite famille',
            9 => 'Mes activités avec ma petite famille',
            10 => 'Mon école',
            11 => 'Des métiers que je connais',
            12 => 'Des ustensiles que j’utilise',
            13 => 'Activités de suivi et de soutien',
            14 => 'Les constructions à la campagne',
            15 => 'Les moyens de transport à la campagne',
            16 => 'La vie à la campagne',
            17 => 'La nature',
            18 => 'Les plantes',
            19 => 'Les arbres fruitiers',
            20 => 'Les habits traditionnels de ma région',
            21 => 'Les festivités dans ma région',
            22 => 'Fête de fin d’année',
            23 => 'Les parties de mon corps',
            24 => 'Les membres de ma grande famille',
            25 => 'Mes activités avec ma grande famille',
            26 => 'L’école et les moyens de communication',
            27 => 'Les moyens technologiques que j’utilise à la maison',
            28 => 'L’ordinateur et la tablette',
            29 => 'Les constructions de ma ville',
            30 => 'Les moyens de transport en ville',
            31 => 'La vie en ville',
            32 => 'La propreté de mon environnement',
            33 => 'Les forêts',
            34 => 'Le boisement',
            35 => 'Les habits traditionnels de mon pays',
            36 => 'Les festivités d’ici et d’ailleurs',
            37 => 'La fête de fin d’année',
            38 => 'Préparation et familiarisation',
            39 => 'Mon hygiène quotidienne',
            40 => 'Je m’habille',
            41 => 'Je prends mon repas',
            42 => 'Suivi et soutien',
            43 => 'Sur le chemin de l’école',
            44 => 'Dans la cour de récréation',
            45 => 'En classe',
            46 => 'Suivi et soutien',
            47 => 'À la maison',
            48 => 'Au jardin',
            49 => 'Au marché',
            50 => 'Suivi et soutien',
            51 => 'Fête de fin d’année',
            52 => 'Introduction à la découverte de soi et au respect des autres',
            53 => 'Introduction aux valeurs entrepreneuriales',
            54 => 'Introduction aux métiers et aux activités quotidiennes',
            55 => 'L’art de créer et d’imaginer',
            56 => 'Naissance du petit roi',
            57 => 'Découverte de soi et de son potentiel entrepreneurial',
            58 => 'Je suis un petit entrepreneur',
            59 => 'Mini-projet de fin de cycle',
            60 => '1er mois',
            61 => '2ème mois'
        ];


        $phases_ar = [
            1 => null,
            2 => null,
            3 => null,
            4 => null,
            5 => null,
            6 => 'استئناس',
            7 => 'أكتشف جسمي',
            8 => 'أفراد أسرتي',
            9 => 'أنشطتي مع أسرتي',
            10 => 'مدرستي',
            11 => 'مهن أعرفها',
            12 => 'أواني أستعملها',
            13 => 'أنشطة التتبع والدعم',
            14 => 'البنايات في القرية',
            15 => 'وسائل النقل في القرية',
            16 => 'الحياة في القرية',
            17 => 'الطبيعة',
            18 => 'النباتات',
            19 => 'الأشجار المثمرة',
            20 => 'اللباس التقليدي ببلدتي',
            21 => 'الاحتفالات في بلدتي',
            22 => 'حفلة نهاية السنة',
            23 => 'أعضاء جسمي',
            24 => 'أفراد عائلتي',
            25 => 'أنشطتي مع عائلتي',
            26 => 'المدرسة ووسائل الاتصال',
            27 => 'أدوات تكنولوجية ببيتنا',
            28 => 'الحاسوب واللوحة الإلكترونية',
            29 => 'البنايات في المدينة',
            30 => 'وسائل النقل في المدينة',
            31 => 'الحياة في المدينة',
            32 => 'نظافة بيئتي',
            33 => 'الغابات',
            34 => 'التشجير',
            35 => 'اللباس التقليدي المغربي',
            36 => 'الاحتفالات في بلدي',
            37 => 'حفلة نهاية السنة',
            38 => 'التهيئة والاستئناس',
            39 => 'نظافتي اليومية',
            40 => 'أرتدي ملابسي',
            41 => 'أتناول وجبتي',
            42 => 'التتبع والدعم',
            43 => 'في طريقي إلى المدرسة',
            44 => 'في ساحة الاستراحة',
            45 => 'في القسم',
            46 => 'التتبع والدعم',
            47 => 'في المنزل',
            48 => 'في الحديقة',
            49 => 'في السوق',
            50 => 'التتبع والدعم',
            51 => 'حفلة نهاية السنة',
            52 => 'مدخل اكتشاف الذات واحترام الآخرين',
            53 => 'مدخل القيم المقاولاتية',
            54 => 'مدخل القيم المقاولاتية',
            55 => 'فن الإبداع والخيال',
            56 => 'ولادة الملك الأول',
            57 => 'اكتشاف الذات والقدرات المقاولاتية',
            58 => 'أنا رائد أعمال صغير',
            59 => 'مشروع صغير واختتام الدورة',
            60 => 'الشهر الأول',
            61 => 'الشهر الثاني',
        ];


        $all_ids = range(1, 61);

        foreach ($all_ids as $id) {
            $tag = null;
            if ($id >= 1 && $id <= 5) $tag = 'project';
            elseif ($id >= 6 && $id <= 22) $tag = 'pre-scolaire';
            elseif ($id >= 23 && $id <= 51) $tag = 'petite-section';
            elseif ($id >= 52 && $id <= 59) $tag = 'petite-section';
            elseif ($id >= 60 && $id <= 61) $tag = "plan d'appui";
            Phase::updateOrCreate(
                ['id' => $id],
                [
                    'name'       => $phases_fr_en[$id] ?? null,
                    'name_arabe' => $phases_ar[$id] ?? null,
                    'tag'        => $tag,
                    'status'     => 'En cours',
                    'created_by' => $users->random()->id,
                ]
            );
        }
    }
}
