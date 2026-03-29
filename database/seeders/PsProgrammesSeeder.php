<?php

namespace Database\Seeders;

use App\Models\PsProgramme;
use Illuminate\Database\Seeder;

class PsProgrammesSeeder extends Seeder
{
    public function run(): void
    {
        $programmes = [
            ['level' => '3', 'subcomponent' => 60, 'name' => 'Écrire correctement les lettres', 'name_ar' => 'كتابة الحروف بشكل صحيح', 'activities' => 'Jeu de tour de rôle', 'activities_ar' => 'لعبة تبادل الأدوار'],
            ['level' => '3', 'subcomponent' => 60, 'name' => 'Soustraction par addition pour trouver le reste', 'name_ar' => 'التمكن من الطرح اعتمادا على عملية الجمع وإيجاد الباقي بالإكمال', 'activities' => "Jeu de l'escargot des nombres", 'activities_ar' => 'لعبة حلزون الارقام'],
            ['level' => '3', 'subcomponent' => 60, 'name' => 'Approcher le concept de multiplication', 'name_ar' => 'التمكن من مقاربة مفهوم الضرب', 'activities' => 'Jeu de mots', 'activities_ar' => 'لعبة الكلمات'],
            ['level' => '3', 'subcomponent' => 60, 'name' => 'Distinguer des sons dans un mot', 'name_ar' => 'تمييز الأصوات في الكلمة', 'activities' => 'Jeu des lettres', 'activities_ar' => 'لعبة الحروف'],
            ['level' => '3', 'subcomponent' => 60, 'name' => 'Réaliser une opération de multiplication', 'name_ar' => 'التمكن من إنجاز عملية الضرب', 'activities' => 'Jeu du nombre précédent et suivant', 'activities_ar' => 'لعبة ما العدد قبلي و الدي يواليني'],
            ['level' => '3', 'subcomponent' => 60, 'name' => 'Comparer et ordonner les nombres', 'name_ar' => 'التمكن من مقارنة وترتيب الأعداد', 'activities' => 'Dessin libre', 'activities_ar' => 'رسم حر'],
            ['level' => '3', 'subcomponent' => 60, 'name' => 'Savoir former des phrases', 'name_ar' => 'معرفة تكوين الجمل', 'activities' => 'Les jours de la semaine', 'activities_ar' => 'أيام الأسبوع'],
            ['level' => '3', 'subcomponent' => 60, 'name' => 'Mesurer des longueurs', 'name_ar' => 'معرفة قياس الأطوال', 'activities' => 'Jeu des nombres', 'activities_ar' => 'لعبة الأعداد'],
            ['level' => '3', 'subcomponent' => 60, 'name' => 'Mesurer des masses', 'name_ar' => 'معرفة قياس الكتل', 'activities' => 'Jeu de mots', 'activities_ar' => 'لعبة الكلمات'],
            ['level' => '3', 'subcomponent' => 60, 'name' => 'Écrire des mots et des phrases', 'name_ar' => 'كتابة الكلمات والجمل', 'activities' => 'Coloriage', 'activities_ar' => 'تلوين'],
            ['level' => '3', 'subcomponent' => 60, 'name' => 'Mesurer des capacités', 'name_ar' => 'معرفة قياس السعات', 'activities' => 'Jeu de l’escargot', 'activities_ar' => 'لعبة حلزون'],
            ['level' => '3', 'subcomponent' => 60, 'name' => 'La droite et le segment de droite', 'name_ar' => 'المستقيم والقطعة المستقيمية', 'activities' => 'Jeu de la ligne', 'activities_ar' => 'لعبة الخط'],

            ['level' => '3', 'subcomponent' => 61, 'name' => 'Reconnaître visuellement et auditivement 2 lettres', 'name_ar' => 'التعرف بصريا وصوتيا على حرفين', 'activities' => 'Coloriage', 'activities_ar' => 'تلوين'],
            ['level' => '3', 'subcomponent' => 61, 'name' => 'Approcher le concept de division', 'name_ar' => 'التمكن من مقارية مفهوم القسمة', 'activities' => 'Jeu des nombres', 'activities_ar' => 'لعبة الأعداد'],
            ['level' => '3', 'subcomponent' => 61, 'name' => 'Discriminer visuellement et auditivement les lettres dans un mot', 'name_ar' => 'تمييز الحروف بصريا وصوتيا في كلمة', 'activities' => 'Jeu des lettres', 'activities_ar' => 'لعبة الحروف'],
            ['level' => '3', 'subcomponent' => 61, 'name' => 'Réaliser une opération de division', 'name_ar' => 'التمكن من إنجاز عملية القسمة', 'activities' => 'Jeu de l’escargot des nombres', 'activities_ar' => 'لعبة حلزون الأرقام'],
            ['level' => '3', 'subcomponent' => 61, 'name' => 'Écrire correctement un mot dans une phrase', 'name_ar' => 'كتابة كلمة بشكل صحيح في جملة', 'activities' => 'Exercice écrit', 'activities_ar' => 'تمرين كتابي'],
            ['level' => '3', 'subcomponent' => 61, 'name' => 'Distinguer les formes géométriques', 'name_ar' => 'التمييز بين الأشكال الهندسية', 'activities' => 'Jeu de mots', 'activities_ar' => 'لعبة الكلمات'],
            ['level' => '3', 'subcomponent' => 61, 'name' => 'Discriminer 2 phonèmes visuellement et auditivement', 'name_ar' => 'تمييز صوتين بصريا وسمعيا', 'activities' => 'Observation', 'activities_ar' => 'ملاحظة'],
            ['level' => '3', 'subcomponent' => 61, 'name' => 'Symétrie axiale', 'name_ar' => 'التماثل المحوري', 'activities' => 'Jeu de la ligne', 'activities_ar' => 'لعبة الخط'],
            ['level' => '3', 'subcomponent' => 61, 'name' => 'Identifier les lieux de l’école', 'name_ar' => 'التعرف على مرافق المدرسة', 'activities' => 'Visite guidée', 'activities_ar' => 'جولة'],
            ['level' => '3', 'subcomponent' => 61, 'name' => 'Parallélisme et perpendicularité', 'name_ar' => 'التوازي و التعامد', 'activities' => 'Construction géométrique', 'activities_ar' => 'إنشاء هندسي'],
            ['level' => '3', 'subcomponent' => 61, 'name' => 'Exprimer la chronologie (avant, après, d’abord)', 'name_ar' => 'التعبير عن الترتيب الزمني', 'activities' => 'Jeu libre', 'activities_ar' => 'لعب حر'],

            ['level' => '4', 'subcomponent' => 60, 'name' => 'Distinguer parallélisme et perpendicularité', 'name_ar' => 'التمييز بن التوازي والتعامد', 'activities' => 'Jeu de nombres', 'activities_ar' => 'لعبة الأعداد'],
            ['level' => '4', 'subcomponent' => 60, 'name' => 'Reconnaître et employer les compléments essentiels : COD/COI', 'name_ar' => 'التعرف على المفعول به المباشر وغير المباشر واستعماله', 'activities' => 'Jeu de tour de rôle', 'activities_ar' => 'لعبة تبادل الأدوار'],
            ['level' => '4', 'subcomponent' => 60, 'name' => 'Distinguer les triangles', 'name_ar' => 'التمييز بين أنواع المثلثات', 'activities' => 'Jeu d’équipe', 'activities_ar' => 'لعبة الفريق'],
            ['level' => '4', 'subcomponent' => 60, 'name' => 'Distinguer les déterminants définis et indéfinis', 'name_ar' => 'التمييز بين أدوات التعريف والتنكير', 'activities' => 'Jeu de mots', 'activities_ar' => 'لعبة الكلمات'],
            ['level' => '4', 'subcomponent' => 60, 'name' => 'Maîtriser les formes géométriques', 'name_ar' => 'أن يتمكن المتعلم بين الأشكال الهندسية', 'activities' => 'Calcul mental', 'activities_ar' => 'الحساب الذهني'],
            ['level' => '4', 'subcomponent' => 60, 'name' => 'Conjuguer les verbes du 1er groupe au passé composé', 'name_ar' => 'تصريف أفعال المجموعة الأولى في الماضي المركب', 'activities' => 'Jeu de mots', 'activities_ar' => 'لعبة الكلمات'],
            ['level' => '4', 'subcomponent' => 60, 'name' => 'Addition et soustraction des décimaux', 'name_ar' => 'التمكن من جمع وطرح الأعداد العشرية', 'activities' => 'Compétition', 'activities_ar' => 'منافسة'],
            ['level' => '4', 'subcomponent' => 60, 'name' => 'Conjuguer les verbes du 2ème groupe au passé composé', 'name_ar' => 'تصريف أفعال المجموعة الثانية في الماضي المركب', 'activities' => 'Chanson', 'activities_ar' => 'أغنية'],
            ['level' => '4', 'subcomponent' => 60, 'name' => 'Lire un texte', 'name_ar' => 'قراءة نص', 'activities' => 'Jeu libre', 'activities_ar' => 'لعب حر'],
            ['level' => '4', 'subcomponent' => 60, 'name' => 'Addition des nombres fractionnaires', 'name_ar' => 'التمكن من إنجاز عملية جمع الأعداد الكسرية', 'activities' => 'Calcul', 'activities_ar' => 'حساب'],
            ['level' => '4', 'subcomponent' => 60, 'name' => 'Reconnaître et utiliser les phrases affirmatives et négatives', 'name_ar' => 'التعرف واستعمال الجمل المثبتة والمنفية', 'activities' => 'Jeu de phrases', 'activities_ar' => 'لعبة الجمل'],
            ['level' => '4', 'subcomponent' => 60, 'name' => 'Soustraction des nombres fractionnaires', 'name_ar' => 'التمكن من إنجاز عملية طرج الأعداد الكسرية', 'activities' => 'Calcul', 'activities_ar' => 'حساب'],

            ['level' => '4', 'subcomponent' => 61, 'name' => 'Multiplication des nombres décimaux et fractionnaires', 'name_ar' => 'إنجاز عملية الضرب للأعداد العشرية والكسرية', 'activities' => 'Jeu de nombres', 'activities_ar' => 'لعبة الأعداد'],
            ['level' => '4', 'subcomponent' => 61, 'name' => 'Découvrir la synonymie', 'name_ar' => 'اكتشاف المرادفات', 'activities' => 'Vocabulaire', 'activities_ar' => 'مفردات'],
            ['level' => '4', 'subcomponent' => 61, 'name' => 'Division des nombres décimaux et fractionnaires', 'name_ar' => 'إنجاز عملية القسمة للأعداد العشرية والكسرية', 'activities' => 'Calcul', 'activities_ar' => 'حساب'],
            ['level' => '4', 'subcomponent' => 61, 'name' => 'Décrire un métier', 'name_ar' => 'وصف مهنة', 'activities' => 'Présentation', 'activities_ar' => 'عرض'],
            ['level' => '4', 'subcomponent' => 61, 'name' => 'Concept de proportionnalité', 'name_ar' => 'التعرف على مفهوم التناسبية', 'activities' => 'Jeu de l’escargot', 'activities_ar' => 'لعبة الحلزون'],
            ['level' => '4', 'subcomponent' => 61, 'name' => 'Connaître les types de phrases', 'name_ar' => 'معرفة أنواع الجمل', 'activities' => 'Identification', 'activities_ar' => 'تحديد'],
            ['level' => '4', 'subcomponent' => 61, 'name' => 'Coefficient de proportionnalité et pourcentage', 'name_ar' => 'التمكن من معرفة معامل التناسب وحساب النسبة المئوية', 'activities' => 'Calcul', 'activities_ar' => 'حساب'],
            ['level' => '4', 'subcomponent' => 61, 'name' => 'Décrire une personne', 'name_ar' => 'وصف شخص', 'activities' => 'Jeu de mots', 'activities_ar' => 'لعبة الكلمات'],
            ['level' => '4', 'subcomponent' => 61, 'name' => 'Mesurer des capacités', 'name_ar' => 'معرفة قياس السعة', 'activities' => 'Mesure', 'activities_ar' => 'قياس'],
            ['level' => '4', 'subcomponent' => 61, 'name' => 'Décrire un lieu', 'name_ar' => 'وصف مكان', 'activities' => 'Chanson', 'activities_ar' => 'أغنية'],
            ['level' => '4', 'subcomponent' => 61, 'name' => 'Le cylindre et le prisme droit', 'name_ar' => 'معرفة الأسطوانة القائمة و الموسور القائم', 'activities' => 'Jeu libre', 'activities_ar' => 'لعب حر'],

            ['level' => '5', 'subcomponent' => 60, 'name' => 'Addition et soustraction des nombres décimaux', 'name_ar' => 'إنجاز عملية الجمع والطرح في نطاق الأعداد العشرية', 'activities' => 'Jeu de nombres', 'activities_ar' => 'لعبة الأعداد'],
            ['level' => '5', 'subcomponent' => 60, 'name' => 'Conjuguer être et avoir au futur simple', 'name_ar' => 'تصريف فعلي الكينونة والملك في المستقبل البسيط', 'activities' => 'Jeu de tour de rôle', 'activities_ar' => 'لعبة تبادل الأدوار'],
            ['level' => '5', 'subcomponent' => 60, 'name' => 'Caractéristiques et construction de formes géométriques', 'name_ar' => 'خاصيات و إنشاءات الأشكال الهندسية', 'activities' => 'Jeu d’équipe', 'activities_ar' => 'لعبة الفريق'],
            ['level' => '5', 'subcomponent' => 60, 'name' => 'Conjuguer les verbes du 1er groupe au futur simple', 'name_ar' => 'تصريف أفعال المجموعة الأولى في المستقبل البسيط', 'activities' => 'Jeu de mots', 'activities_ar' => 'لعبة الكلمات'],
            ['level' => '5', 'subcomponent' => 60, 'name' => 'Division euclidienne des entiers naturels', 'name_ar' => 'إنجاز القسمة الأقليدية في نطاق الأعداد الصحيحة الطبيعية', 'activities' => 'Calcul mental', 'activities_ar' => 'الحساب الذهني'],
            ['level' => '5', 'subcomponent' => 60, 'name' => 'Conjuguer les verbes du 2ème groupe au futur simple', 'name_ar' => 'تصريف أفعال المجموعة الثانية في المستقبل البسيط', 'activities' => 'Jeu de mots', 'activities_ar' => 'لعبة الكلمات'],
            ['level' => '5', 'subcomponent' => 60, 'name' => 'Multiplication des nombres décimaux', 'name_ar' => 'إنجاز عملية الضرب للأعداد العشرية', 'activities' => 'Calcul', 'activities_ar' => 'حساب'],
            ['level' => '5', 'subcomponent' => 60, 'name' => 'Les pronoms démonstratifs', 'name_ar' => 'أسماء الإشارة', 'activities' => 'Chanson', 'activities_ar' => 'أغنية'],
            ['level' => '5', 'subcomponent' => 60, 'name' => 'Division des nombres décimaux', 'name_ar' => 'قسمة الأعداد العشرية', 'activities' => 'Calcul', 'activities_ar' => 'حساب'],
            ['level' => '5', 'subcomponent' => 60, 'name' => 'Les suffixes', 'name_ar' => 'اللواحق', 'activities' => 'Théâtre', 'activities_ar' => 'مسرح'],
            ['level' => '5', 'subcomponent' => 60, 'name' => 'Calcul de l’aire des formes', 'name_ar' => 'حساب قياس مساحة الأشكال', 'activities' => 'Calcul', 'activities_ar' => 'حساب'],
            ['level' => '5', 'subcomponent' => 60, 'name' => 'Les préfixes', 'name_ar' => 'السوابق', 'activities' => 'Deviner le mot', 'activities_ar' => 'حزر الكلمة'],

            ['level' => '5', 'subcomponent' => 61, 'name' => 'Reconnaître les nombres fractionnaires', 'name_ar' => 'التعرف على الأعداد الكسرية', 'activities' => 'Identification', 'activities_ar' => 'تحديد'],
            ['level' => '5', 'subcomponent' => 61, 'name' => 'Exprimer un point de vue', 'name_ar' => 'التعبير عن وجهة نظر', 'activities' => 'Jeu des 4 images', 'activities_ar' => 'لعبة 4 صور'],
            ['level' => '5', 'subcomponent' => 61, 'name' => 'Addition des nombres fractionnaires', 'name_ar' => 'التمكن من جمع الأعداد الكسرية', 'activities' => 'Calcul', 'activities_ar' => 'حساب'],
            ['level' => '5', 'subcomponent' => 61, 'name' => 'Chercher le sens dans un dictionnaire', 'name_ar' => 'البحث عن المعنى في المعجم', 'activities' => 'Chanson', 'activities_ar' => 'أغنية'],
            ['level' => '5', 'subcomponent' => 61, 'name' => 'Soustraction des nombres fractionnaires', 'name_ar' => 'طرح الأعداد الكسرية', 'activities' => 'Calcul', 'activities_ar' => 'حساب'],
            ['level' => '5', 'subcomponent' => 61, 'name' => 'Noms et déterminants', 'name_ar' => 'الأسماء والمحددات', 'activities' => 'Jeu de rôle', 'activities_ar' => 'لعبة تبادل الأدوار'],
            ['level' => '5', 'subcomponent' => 61, 'name' => 'Multiplication des nombres fractionnaires', 'name_ar' => 'إنجاز عملية ضرب الأعداد الكسرية', 'activities' => 'Jeu de l’escargot', 'activities_ar' => 'لعبة الحلزون'],
            ['level' => '5', 'subcomponent' => 61, 'name' => 'Le genre et le nombre', 'name_ar' => 'الجنس والعدد', 'activities' => 'Jeu d’images', 'activities_ar' => 'لعبة الصور'],
            ['level' => '5', 'subcomponent' => 61, 'name' => 'Multiples et diviseurs', 'name_ar' => 'معرفة المضاعفات والقواسم', 'activities' => 'Calcul', 'activities_ar' => 'حساب'],
            ['level' => '5', 'subcomponent' => 61, 'name' => 'Pronoms démonstratifs et possessifs', 'name_ar' => 'أسماء الإشارة والملك', 'activities' => 'Jeu libre', 'activities_ar' => 'لعب حر'],

            ['level' => '6', 'subcomponent' => 60, 'name' => 'Multiplication des nombres décimaux et fractionnaires', 'name_ar' => 'إنجاز عملية الضرب للأعداد العشرية والكسرية', 'activities' => 'Jeu de nombres', 'activities_ar' => 'لعبة الأعداد'],
            ['level' => '6', 'subcomponent' => 60, 'name' => 'Accord de l’adjectif qualificatif', 'name_ar' => 'توافق النعت والمنعوت', 'activities' => 'Jeu de tour de rôle', 'activities_ar' => 'لعبة تبادل الأدوار'],
            ['level' => '6', 'subcomponent' => 60, 'name' => 'Division des nombres décimaux et fractionnaires', 'name_ar' => 'إنجاز عملية القسمة للأعداد العشرية والكسرية', 'activities' => 'Jeu d’équipe', 'activities_ar' => 'لعبة الفريق'],
            ['level' => '6', 'subcomponent' => 60, 'name' => 'Le genre et le nombre', 'name_ar' => 'معرفة الجنس والعدد', 'activities' => 'Jeu de mots', 'activities_ar' => 'لعبة الكلمات'],
            ['level' => '6', 'subcomponent' => 60, 'name' => 'Concept de proportionnalité', 'name_ar' => 'التعرف على مفهوم التناسبية', 'activities' => 'Calcul mental', 'activities_ar' => 'الحساب الذهني'],
            ['level' => '6', 'subcomponent' => 60, 'name' => 'Phrases affirmatives et négatives', 'name_ar' => 'الجمل المثبتة والمنفية', 'activities' => 'Jeu de mots', 'activities_ar' => 'لعبة الكلمات'],
            ['level' => '6', 'subcomponent' => 60, 'name' => 'Calcul du pourcentage', 'name_ar' => 'حساب النسبة المئوية', 'activities' => 'Calcul', 'activities_ar' => 'حساب'],
            ['level' => '6', 'subcomponent' => 60, 'name' => 'Reconnaître les COD/COI', 'name_ar' => 'التعرف واستعمال المفعول به المباشر وغير المباشر', 'activities' => 'Chanson', 'activities_ar' => 'أغنية'],
            ['level' => '6', 'subcomponent' => 60, 'name' => 'Mesure des capacités', 'name_ar' => 'معرفة قياس السعة', 'activities' => 'Jeu libre', 'activities_ar' => 'لعب حر'],
            ['level' => '6', 'subcomponent' => 60, 'name' => 'Déterminants définis et indéfinis', 'name_ar' => 'تمييز أدوات التعريف والتنكير', 'activities' => 'Identification', 'activities_ar' => 'تحديد'],
            ['level' => '6', 'subcomponent' => 60, 'name' => 'Cylindre et prisme droit', 'name_ar' => 'معرفة الأسطوانة القائمة و الموسور القائم', 'activities' => 'Jeu de mot', 'activities_ar' => 'لعبة الكلمات'],
            ['level' => '6', 'subcomponent' => 60, 'name' => 'Système sexagésimal (temps)', 'name_ar' => 'معرفة الأعداد الستينية', 'activities' => 'Jeu de mot', 'activities_ar' => 'لعبة الكلمات'],

            ['level' => '6', 'subcomponent' => 61, 'name' => 'Former des mots avec des préfixes', 'name_ar' => 'تكوين كلمات باستعمال السوابق', 'activities' => 'Jeu de nombres', 'activities_ar' => 'لعبة الأعداد'],
            ['level' => '6', 'subcomponent' => 61, 'name' => 'Calcul du périmètre et de l’aire', 'name_ar' => 'حساب مساحة و محيط الأشكال الهندسية', 'activities' => 'Calcul', 'activities_ar' => 'حساب'],
            ['level' => '6', 'subcomponent' => 61, 'name' => 'Lire un texte narratif', 'name_ar' => 'قراءة نص سردي', 'activities' => 'Jeu de lecture', 'activities_ar' => 'لعبة القراءة'],
            ['level' => '6', 'subcomponent' => 61, 'name' => 'Calcul de la vitesse moyenne', 'name_ar' => 'حساب السرعة المتوسطة', 'activities' => 'Calcul', 'activities_ar' => 'حساب'],
            ['level' => '6', 'subcomponent' => 61, 'name' => 'Accorder le verbe avec son sujet', 'name_ar' => 'توافق الفعل مع الفاعل', 'activities' => 'Jeu de l’escargot', 'activities_ar' => 'لعبة الحلزون'],
            ['level' => '6', 'subcomponent' => 61, 'name' => 'Échelles de plans et cartes', 'name_ar' => 'التعرف على سلم التصاميم و الخرائط', 'activities' => 'Mesure', 'activities_ar' => 'قياس'],
            ['level' => '6', 'subcomponent' => 61, 'name' => 'Utiliser un champ lexical', 'name_ar' => 'استعمال المجال المعجمي', 'activities' => 'Vocabulaire', 'activities_ar' => 'مفردات'],
            ['level' => '6', 'subcomponent' => 61, 'name' => 'Division des nombres décimaux', 'name_ar' => 'إنجاز عملية القسمة للأعداد العشرية', 'activities' => 'Jeu de mot', 'activities_ar' => 'لعبة الكلمات'],
            ['level' => '6', 'subcomponent' => 61, 'name' => 'Lire un texte narratif (suite)', 'name_ar' => 'قراءة نص سردي', 'activities' => 'Analyse', 'activities_ar' => 'تحليل'],
            ['level' => '6', 'subcomponent' => 61, 'name' => 'Concept de proportionnalité approfondi', 'name_ar' => 'لتمكن من معرفة مفهوم التناسبية', 'activities' => 'Jeu de calcul', 'activities_ar' => 'لعبة الحساب'],
        ];

        foreach ($programmes as $prog) {
            PsProgramme::create($prog);
        }
    }
}
