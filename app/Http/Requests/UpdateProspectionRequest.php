<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProspectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            // Identification
            'prospections_id' => [
                'nullable',
                'integer',
                Rule::unique('prospections', 'prospections_id')->ignore($this->route('prospection'))
            ],

            'program_id' => 'required|exists:programs,id',
            'site_id'    => 'required|exists:sites,id',

            'title' => 'required|string|max:255',

            // Zone
            'douar_access' => 'nullable|in:route_goudronnee,accessible_en_voiture,accessible_uniquement_en_4x4,accessible_en_moto,accessible_uniquement_a_pied',
            'distance_to_nearest_douar_km' => 'nullable|numeric',
            'distance_to_nearest_city_km'  => 'nullable|numeric',
            'main_language' => 'nullable|in:arabe_darija,amazigh,arabe_classique,francais,anglais',

            // Population
            'households'        => 'nullable|integer|min:0',
            'total_population'  => 'nullable|integer|min:0',
            'literacy_rate'     => 'nullable|numeric|min:0|max:100',
            'schooling_rate'    => 'nullable|numeric|min:0|max:100',

            'children_0_5_b'   => 'nullable|integer|min:0',
            'children_0_5_g'   => 'nullable|integer|min:0',
            'children_6_12_b'  => 'nullable|integer|min:0',
            'children_6_12_g'  => 'nullable|integer|min:0',
            'children_13_18_b' => 'nullable|integer|min:0',
            'children_13_18_g' => 'nullable|integer|min:0',

            'youth_m' => 'nullable|integer|min:0',
            'youth_f' => 'nullable|integer|min:0',

            'adults_m' => 'nullable|integer|min:0',
            'adults_f' => 'nullable|integer|min:0',
            'remarque' => 'nullable|string',

            // Accès services
            'preschool' => 'boolean',
            'preschool_count' => 'nullable|integer|min:0',

            'primary_school' => 'boolean',
            'primary_school_count' => 'nullable|integer|min:0',

            'middle_school' => 'boolean',
            'middle_school_count' => 'nullable|integer|min:0',

            'high_school' => 'boolean',
            'high_school_count' => 'nullable|integer|min:0',

            'second_chance_center' => 'boolean',
            'second_chance_center_count' => 'nullable|integer|min:0',

            'health_center' => 'boolean',
            'health_center_count' => 'nullable|integer|min:0',

            'pharmacy' => 'boolean',
            'pharmacy_count' => 'nullable|integer|min:0',

            'training_center' => 'boolean',
            'training_center_count' => 'nullable|integer|min:0',

            'literacy_center' => 'boolean',
            'literacy_center_count' => 'nullable|integer|min:0',

            'guidance_center' => 'boolean',
            'guidance_center_count' => 'nullable|integer|min:0',

            'youth_house' => 'boolean',
            'youth_house_count' => 'nullable|integer|min:0',

            'active_associations' => 'boolean',
            'association_names' => 'nullable|string',
            'association_activity_type' => 'nullable|in:aide_humanitaire_et_assistance,insertion_sociale_et_professionnelle,service_a_la_personne,education_et_formation,activites_socioculturelles_et_sportives',
            'association_members_count' => 'nullable|integer|min:0',
            'association_contact' => 'nullable|string|max:255',

            'educational_clubs' => 'boolean',
            'educational_clubs_count' => 'nullable|integer|min:0',

            'sports_space' => 'boolean',
            'sports_space_count' => 'nullable|integer|min:0',

            'school_transport' => 'boolean',
            'transport_provider' => 'nullable|string|max:255',

            'student_accommodation' => 'boolean',
            'student_accommodation_count' => 'nullable|integer|min:0',

            'economic_activities' => 'nullable|string',
            'cultural_activities' => 'nullable|string',

            // Local prospecté
            'local_name' => 'nullable|string|max:255',

            'owner_type' => 'nullable|in:public,prive,associatif,physique,autre',
            'owner_status' => 'nullable|in:indh,aref,entraide_national,maison_des_jeunes,commune,association,particulier,autre',

            'manager_status' => 'nullable|in:public,prive,associatif,physique,autre',
            'manager_structure' => 'nullable|in:indh,aref,entraide_national,maison_des_jeunes,commune,association,particulier,autre',

            'surface_area' => 'nullable|integer|min:0',
            'available_rooms_count' => 'nullable|integer|min:0',
            'training_rooms_count' => 'nullable|integer|min:0',
            'management_rooms_count' => 'nullable|integer|min:0',
            'sanitary_blocks_count' => 'nullable|integer|min:0',

            'water_connection' => 'boolean',
            'electricity_connection' => 'boolean',
            'internet_access' => 'boolean',
            'security' => 'boolean',

            'needs_renovation' => 'boolean',
            'renovation_type' => 'nullable|string',

            // Equipements
            'has_equipment' => 'boolean',

            // Equipements avec condition
            // (généralisés pour ne pas répéter 40 fois)
            '*.count' => 'nullable|integer|min:0',
            '*.condition' => 'nullable|in:good,bad',

            // Équipements spécifiques
            'individual_tables_count' => 'nullable|integer|min:0',
            'individual_tables_condition' => 'nullable|in:good,bad',

            'collective_tables_count' => 'nullable|integer|min:0',
            'collective_tables_condition' => 'nullable|in:good,bad',

            'boards_count' => 'nullable|integer|min:0',
            'boards_condition' => 'nullable|in:good,bad',

            'display_boards_count' => 'nullable|integer|min:0',
            'display_boards_condition' => 'nullable|in:good,bad',

            'coat_hangers_count' => 'nullable|integer|min:0',
            'coat_hangers_condition' => 'nullable|in:good,bad',

            'teacher_desks_count' => 'nullable|integer|min:0',
            'teacher_desks_condition' => 'nullable|in:good,bad',

            'teacher_chairs_count' => 'nullable|integer|min:0',
            'teacher_chairs_condition' => 'nullable|in:good,bad',

            'children_chairs_count' => 'nullable|integer|min:0',
            'children_chairs_condition' => 'nullable|in:good,bad',

            'student_chairs_count' => 'nullable|integer|min:0',
            'student_chairs_condition' => 'nullable|in:good,bad',

            'cabinets_count' => 'nullable|integer|min:0',
            'cabinets_condition' => 'nullable|in:good,bad',

            'storage_wardrobes_count' => 'nullable|integer|min:0',
            'storage_wardrobes_condition' => 'nullable|in:good,bad',

            'libraries_count' => 'nullable|integer|min:0',
            'libraries_condition' => 'nullable|in:good,bad',

            'extinguishers_count' => 'nullable|integer|min:0',
            'extinguishers_condition' => 'nullable|in:good,bad',

            'desktop_computers_count' => 'nullable|integer|min:0',
            'desktop_computers_condition' => 'nullable|in:good,bad',

            'laptops_count' => 'nullable|integer|min:0',
            'laptops_condition' => 'nullable|in:good,bad',

            'printers_count' => 'nullable|integer|min:0',
            'printers_condition' => 'nullable|in:good,bad',

            'copiers_count' => 'nullable|integer|min:0',
            'copiers_condition' => 'nullable|in:good,bad',

            'video_projectors_count' => 'nullable|integer|min:0',
            'video_projectors_condition' => 'nullable|in:good,bad',

            'projection_screens_count' => 'nullable|integer|min:0',
            'projection_screens_condition' => 'nullable|in:good,bad',

            'interactive_boards_count' => 'nullable|integer|min:0',
            'interactive_boards_condition' => 'nullable|in:good,bad',

            'other_equipment_count' => 'nullable|integer|min:0',
            'other_equipment_condition' => 'nullable|in:good,bad',
            'other_equipment_description' => 'nullable|string',

            'has_outdoor_spaces' => 'boolean',
            'other_local_to_prospect' => 'nullable|string',

            'equipment_observations' => 'nullable|string',

            'decision' => 'nullable|in:favorable,to_be_investigated,not_favorable',

            'potential_projects' => 'nullable|array',
            'potential_projects.*' => 'string',

            'final_observations' => 'nullable|string'
        ];
    }
}
