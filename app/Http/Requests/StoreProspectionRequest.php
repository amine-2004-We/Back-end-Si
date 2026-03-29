<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProspectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // À ajuster selon votre logique d'autorisation
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Identification
            'program_id' => 'required|exists:programs,id',
            'site_id' => 'required|exists:sites,id',
            'title' => 'required|string|max:255',
            
            // Zone
            'douar_access' => [
                'required',
                Rule::in([
                    'route_goudronnee',
                    'accessible_en_voiture',
                    'accessible_uniquement_en_4x4',
                    'accessible_en_moto',
                    'accessible_uniquement_a_pied',
                ])
            ],
            'distance_to_nearest_douar_km' => 'nullable|numeric|min:0',
            'distance_to_nearest_city_km' => 'nullable|numeric|min:0',
            'main_language' => [
                'required',
                Rule::in([
                    'arabe_darija',
                    'amazigh',
                    'arabe_classique',
                    'francais',
                    'anglais',
                ])
            ],
            
            // Caractéristiques de la population
            'households' => 'nullable|integer|min:0',
            'total_population' => 'nullable|integer|min:0',
            'literacy_rate' => 'nullable|numeric|between:0,100',
            'schooling_rate' => 'nullable|numeric|between:0,100',
            
            // Enfants par âge et genre
            'children_0_5_b' => 'nullable|integer|min:0',
            'children_0_5_g' => 'nullable|integer|min:0',
            'children_6_12_b' => 'nullable|integer|min:0',
            'children_6_12_g' => 'nullable|integer|min:0',
            'children_13_18_b' => 'nullable|integer|min:0',
            'children_13_18_g' => 'nullable|integer|min:0',
            
            // Jeunes et adultes
            'youth_m' => 'nullable|integer|min:0',
            'youth_f' => 'nullable|integer|min:0',
            'adults_m' => 'nullable|integer|min:0',
            'adults_f' => 'nullable|integer|min:0',
            'remarque' => 'nullable|string',
            
            // Équipements éducatifs
            'preschool' => 'required|boolean',
            'preschool_count' => 'nullable|integer|min:0',
            'primary_school' => 'required|boolean',
            'primary_school_count' => 'nullable|integer|min:0',
            'middle_school' => 'required|boolean',
            'middle_school_count' => 'nullable|integer|min:0',
            'high_school' => 'required|boolean',
            'high_school_count' => 'nullable|integer|min:0',
            'second_chance_center' => 'required|boolean',
            'second_chance_center_count' => 'nullable|integer|min:0',
            'health_center' => 'required|boolean',
            'health_center_count' => 'nullable|integer|min:0',
            'pharmacy' => 'required|boolean',
            'pharmacy_count' => 'nullable|integer|min:0',
            'training_center' => 'required|boolean',
            'training_center_count' => 'nullable|integer|min:0',
            'literacy_center' => 'boolean',
            'literacy_center_count' => 'nullable|integer|min:0',
            'guidance_center' => 'boolean',
            'guidance_center_count' => 'nullable|integer|min:0',
            'youth_house' => 'required|boolean',
            'youth_house_count' => 'nullable|integer|min:0',
            
            // Associations
            'active_associations' => 'boolean',
            'association_names' => 'nullable|string',
            'association_activity_type' => [
                'nullable',
                Rule::in([
                    'aide_humanitaire_et_assistance',
                    'insertion_sociale_et_professionnelle',
                    'service_a_la_personne',
                    'education_et_formation',
                    'activites_socioculturelles_et_sportives',
                ])
            ],
            'association_members_count' => 'nullable|integer|min:0',
            'association_contact' => 'nullable|string|max:255',
            
            // Activités éducatives et sportives
            'educational_clubs' => 'required|boolean',
            'educational_clubs_count' => 'nullable|integer|min:0',
            'sports_space' => 'required|boolean',
            'sports_space_count' => 'nullable|integer|min:0',
            
            // Transport et logement
            'school_transport' => 'required|boolean',
            'transport_provider' => 'nullable|string|max:255',
            'student_accommodation' => 'required|boolean',
            'student_accommodation_count' => 'nullable|integer|min:0',
            
            // Activités locales
            'economic_activities' => 'required|string',
            'cultural_activities' => 'required|string',
            
            // Local prospecté
            'local_name' => 'nullable|string|max:255',
            'owner_type' => [
                'nullable',
                Rule::in(['public', 'prive', 'associatif', 'physique', 'autre'])
            ],
            'owner_status' => [
                'nullable',
                Rule::in(['indh', 'aref', 'entraide_national', 'maison_des_jeunes', 'commune', 'association', 'particulier', 'autre'])
            ],
            'manager_status' => [
                'nullable',
                Rule::in(['public', 'prive', 'associatif', 'physique', 'autre'])
            ],
            'manager_structure' => [
                'nullable',
                Rule::in(['indh', 'aref', 'entraide_national', 'maison_des_jeunes', 'commune', 'association', 'particulier', 'autre'])
            ],
            
            // Caractéristiques du local
            'surface_area' => 'nullable|integer|min:0',
            'available_rooms_count' => 'nullable|integer|min:0',
            'training_rooms_count' => 'nullable|integer|min:0',
            'management_rooms_count' => 'nullable|integer|min:0',
            'sanitary_blocks_count' => 'nullable|integer|min:0',
            
            // Infrastructure
            'water_connection' => 'boolean',
            'electricity_connection' => 'boolean',
            'internet_access' => 'boolean',
            'security' => 'boolean',
            
            // Rénovation
            'needs_renovation' => 'boolean',
            'renovation_type' => 'nullable|string',
            
            // Équipements
            'has_equipment' => 'boolean',
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
            
            // Espaces extérieurs
            'has_outdoor_spaces' => 'boolean',
            'other_local_to_prospect' => 'nullable|string',
            
            // Observations
            'equipment_observations' => 'nullable|string',
            
            // Décision
            'decision' => [
                'nullable',
                Rule::in([
                    'favorable',
                    'to_be_investigated',
                    'not_favorable'
                ])
            ],
            
            // Projets potentiels
            'potential_projects' => 'nullable|array',
            'potential_projects.*' => 'string|max:255',
            
            // Observations finales
            'final_observations' => 'nullable|string',
        // 'equipment_observations' => 'nullable|string',
        // 'other_local_to_prospect' => 'nullable|string',
        // 'other_equipment_description' => 'nullable|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'required' => 'Le champ :attribute est obligatoire.',
            'in' => 'La valeur sélectionnée pour :attribute est invalide.',
            'numeric' => 'Le champ :attribute doit être un nombre.',
            'min' => 'La valeur du champ :attribute doit être supérieure ou égale à :min.',
            'between' => 'La valeur du champ :attribute doit être comprise entre :min et :max.',
            'array' => 'Le champ :attribute doit être un tableau.',
            'string' => 'Le champ :attribute doit être une chaîne de caractères.',
            'boolean' => 'Le champ :attribute doit être vrai ou faux.',
            'exists' => 'La valeur sélectionnée pour :attribute est invalide.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'program_id' => 'programme',
            'site_id' => 'site',
            'title' => 'titre',
            'douar_access' => 'accès au douar',
            'main_language' => 'langue principale',
            'potential_projects' => 'projets potentiels',
            // Ajoutez d'autres attributs personnalisés au besoin
        ];
    }
}