<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prospection extends Model
{
    use SoftDeletes;

    /**
     * Table name
     */
    protected $table = 'prospections';

    /**
     * Douar access options constants
     */
    public const DOUAR_ACCESS_OPTIONS = [
        'route_goudronnee',
        'accessible_en_voiture',
        'accessible_uniquement_en_4x4',
        'accessible_en_moto',
        'accessible_uniquement_a_pied',
    ];

    public const DOUAR_ACCESS_LABELS = [
        'route_goudronnee' => 'Route goudronnée',
        'accessible_en_voiture' => 'Accessible en voiture',
        'accessible_uniquement_en_4x4' => 'Accessible uniquement en 4x4',
        'accessible_en_moto' => 'Accessible en moto',
        'accessible_uniquement_a_pied' => 'Accessible uniquement à pied',
    ];

    public static function douarAccessOptions(): array
    {
        return self::DOUAR_ACCESS_OPTIONS;
    }

    public static function douarAccessLabels(): array
    {
        return self::DOUAR_ACCESS_LABELS;
    }

    /**
     * Main language options
     */
    public const MAIN_LANGUAGE_OPTIONS = [
        'arabe_darija',
        'amazigh',
        'arabe_classique',
        'francais',
        'anglais',
    ];

    public const MAIN_LANGUAGE_LABELS = [
        'arabe_darija' => 'Arabe Darija',
        'amazigh' => 'Amazigh',
        'arabe_classique' => 'Arabe Classique',
        'francais' => 'Français',
        'anglais' => 'Anglais',
    ];

    public static function mainLanguageOptions(): array
    {
        return self::MAIN_LANGUAGE_OPTIONS;
    }

    public static function mainLanguageLabels(): array
    {
        return self::MAIN_LANGUAGE_LABELS;
    }

    /**
     * Association activity type options
     */
    public const ASSOCIATION_ACTIVITY_TYPE_OPTIONS = [
        'aide_humanitaire_et_assistance',
        'insertion_sociale_et_professionnelle',
        'service_a_la_personne',
        'education_et_formation',
        'activites_socioculturelles_et_sportives',
    ];

    public const ASSOCIATION_ACTIVITY_TYPE_LABELS = [
        'aide_humanitaire_et_assistance' => 'Aide humanitaire et assistance',
        'insertion_sociale_et_professionnelle' => 'Insertion sociale et professionnelle',
        'service_a_la_personne' => 'Service à la personne',
        'education_et_formation' => 'Education et formation',
        'activites_socioculturelles_et_sportives' => 'Activités socioculturelles et sportives',
    ];

    public static function associationActivityTypeOptions(): array
    {
        return self::ASSOCIATION_ACTIVITY_TYPE_OPTIONS;
    }

    public static function associationActivityTypeLabels(): array
    {
        return self::ASSOCIATION_ACTIVITY_TYPE_LABELS;
    }

    /**
     * Owner type options
     */
    public const OWNER_TYPE_OPTIONS = [
        'public',
        'prive',
        'associatif',
        'physique',
        'autre',
    ];

    public const OWNER_TYPE_LABELS = [
        'public' => 'Public',
        'prive' => 'Privé',
        'associatif' => 'Associatif',
        'physique' => 'Physique',
        'autre' => 'Autre',
    ];

    public static function ownerTypeOptions(): array
    {
        return self::OWNER_TYPE_OPTIONS;
    }

    public static function ownerTypeLabels(): array
    {
        return self::OWNER_TYPE_LABELS;
    }

    /**
     * Owner status options
     */
    public const OWNER_STATUS_OPTIONS = [
        'indh',
        'aref',
        'entraide_national',
        'maison_des_jeunes',
        'commune',
        'association',
        'particulier',
        'autre',
    ];

    public const OWNER_STATUS_LABELS = [
        'indh' => 'INDH',
        'aref' => 'AREF',
        'entraide_national' => 'Entraide Nationale',
        'maison_des_jeunes' => 'Maison des Jeunes',
        'commune' => 'Commune',
        'association' => 'Association',
        'particulier' => 'Particulier',
        'autre' => 'Autre',
    ];

    public static function ownerStatusOptions(): array
    {
        return self::OWNER_STATUS_OPTIONS;
    }

    public static function ownerStatusLabels(): array
    {
        return self::OWNER_STATUS_LABELS;
    }

    /**
     * Manager type options
     */
    public const MANAGER_STATUS_OPTIONS = [
        'public',
        'prive',
        'associatif',
        'physique',
        'autre',
    ];

    public const MANAGER_STATUS_LABELS = [
        'public' => 'Public',
        'prive' => 'Privé',
        'associatif' => 'Associatif',
        'physique' => 'Physique',
        'autre' => 'Autre',
    ];

    public static function managerStatusOptions(): array
    {
        return self::MANAGER_STATUS_OPTIONS;
    }

    public static function managerStatusLabels(): array
    {
        return self::MANAGER_STATUS_LABELS;
    }

    /**
     * Manager structure options
     */
    public const MANAGER_STRUCTURE_OPTIONS = [
        'indh',
        'aref',
        'entraide_national',
        'maison_des_jeunes',
        'commune',
        'association',
        'particulier',
        'autre',
    ];

    public const MANAGER_STRUCTURE_LABELS = [
        'indh' => 'INDH',
        'aref' => 'AREF',
        'entraide_national' => 'Entraide Nationale',
        'maison_des_jeunes' => 'Maison des Jeunes',
        'commune' => 'Commune',
        'association' => 'Association',
        'particulier' => 'Particulier',
        'autre' => 'Autre',
    ];

    public static function managerStructureOptions(): array
    {
        return self::MANAGER_STRUCTURE_OPTIONS;
    }

    public static function managerStructureLabels(): array
    {
        return self::MANAGER_STRUCTURE_LABELS;
    }

    /**
     * Equipment condition options (good/bad)
     */
    public const CONDITION_OPTIONS = [
        'good',
        'bad',
    ];

    public const CONDITION_LABELS = [
        'good' => 'Good',
        'bad' => 'Bad',
    ];

    public static function conditionOptions(): array
    {
        return self::CONDITION_OPTIONS;
    }

    public static function conditionLabels(): array
    {
        return self::CONDITION_LABELS;
    }

    /**
     * Decision options
     */
    public const DECISION_OPTIONS = [
        'favorable',
        'to_be_investigated',
        'not_favorable',
    ];

    public const DECISION_LABELS = [
        'favorable' => 'Favorable',
        'to_be_investigated' => 'To Be Investigated',
        'not_favorable' => 'Not Favorable',
    ];

    public static function decisionOptions(): array
    {
        return self::DECISION_OPTIONS;
    }

    public static function decisionLabels(): array
    {
        return self::DECISION_LABELS;
    }

    /**
     * Mass assignable fields.
     * Liste organisée par domaines métiers pour plus de lisibilité.
     */
    protected $fillable = [

        'prospections_id',
        'program_id',
        'site_id',
        'prospector_id',
        'title',

        // Zone 
        'douar_access',
        'distance_to_nearest_douar_km',
        'distance_to_nearest_city_km',
        'main_language',

        //caracteristique de la population
        'households',
        'total_population',
        'literacy_rate',
        'schooling_rate',

        'children_0_5_b', 'children_0_5_g', 'children_0_5_total',
        'children_6_12_b', 'children_6_12_g', 'children_6_12_total',
        'children_13_18_b', 'children_13_18_g', 'children_13_18_total',
        'children_total',

        'youth_m', 'youth_f', 'youth_total',
        'adults_m', 'adults_f', 'adults_total',

        // Accès éducation & services
        'preschool', 'preschool_count',
        'primary_school', 'primary_school_count',
        'middle_school', 'middle_school_count',
        'high_school', 'high_school_count',
        'second_chance_center', 'second_chance_center_count',
        'health_center', 'health_center_count',
        'pharmacy', 'pharmacy_count',
        'training_center', 'training_center_count',
        'literacy_center', 'literacy_center_count',
        'guidance_center', 'guidance_center_count',
        'youth_house', 'youth_house_count',

        'active_associations',
        'association_names',
        'association_activity_type',
        'association_members_count',
        'association_contact',

        'educational_clubs', 'educational_clubs_count',
        'sports_space', 'sports_space_count',
        'school_transport', 'transport_provider',
        'student_accommodation', 'student_accommodation_count',

        'economic_activities',
        'cultural_activities',

        // Local characteristics
        'local_name',
        'owner_type',
        'owner_status',
        'manager_status',
        'manager_structure',
        'surface_area',
        'available_rooms_count',
        'training_rooms_count',
        'management_rooms_count',
        'sanitary_blocks_count',
        'water_connection',
        'electricity_connection',
        'internet_access',
        'security',
        'needs_renovation',
        'renovation_type',

        // Equipment
        'has_equipment',

        'individual_tables_count', 'individual_tables_condition',
        'collective_tables_count', 'collective_tables_condition',
        'boards_count', 'boards_condition',
        'display_boards_count', 'display_boards_condition',
        'coat_hangers_count', 'coat_hangers_condition',
        'teacher_desks_count', 'teacher_desks_condition',
        'teacher_chairs_count', 'teacher_chairs_condition',
        'children_chairs_count', 'children_chairs_condition',
        'student_chairs_count', 'student_chairs_condition',
        'cabinets_count', 'cabinets_condition',
        'storage_wardrobes_count', 'storage_wardrobes_condition',
        'libraries_count', 'libraries_condition',
        'extinguishers_count', 'extinguishers_condition',
        'desktop_computers_count', 'desktop_computers_condition',
        'laptops_count', 'laptops_condition',
        'printers_count', 'printers_condition',
        'copiers_count', 'copiers_condition',
        'video_projectors_count', 'video_projectors_condition',
        'projection_screens_count', 'projection_screens_condition',
        'interactive_boards_count', 'interactive_boards_condition',
        'other_equipment_count', 'other_equipment_condition',
        'other_equipment_description',

        // Outdoor
        'has_outdoor_spaces',

        // Other local
        'other_local_to_prospect',
        'equipment_observations',

        // Decision & final
        'decision',
        'potential_projects',
        'final_observations',
        'created_by',
        'updated_by',

        'remarque',
    ];

    /**
     * Casts for structured fields.
     */
    protected $casts = [
        'preschool' => 'boolean',
        'primary_school' => 'boolean',
        'middle_school' => 'boolean',
        'high_school' => 'boolean',
        'second_chance_center' => 'boolean',
        'health_center' => 'boolean',
        'pharmacy' => 'boolean',
        'training_center' => 'boolean',
        'literacy_center' => 'boolean',
        'guidance_center' => 'boolean',
        'youth_house' => 'boolean',
        'active_associations' => 'boolean',
        'educational_clubs' => 'boolean',
        'sports_space' => 'boolean',
        'school_transport' => 'boolean',
        'student_accommodation' => 'boolean',
        'water_connection' => 'boolean',
        'electricity_connection' => 'boolean',
        'internet_access' => 'boolean',
        'security' => 'boolean',
        'needs_renovation' => 'boolean',
        'has_equipment' => 'boolean',
        'has_outdoor_spaces' => 'boolean',

        // JSON structured
        'potential_projects' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relations
     */

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function prospector(): BelongsTo
    {
        return $this->belongsTo(Collaborator::class, 'prospector_id');
    }

    // === VALIDATION WORKFLOW RELATIONS ===
    
    public function supervisorReviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_reviewer_id');
    }

    public function operationalReviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operational_reviewer_id');
    }

    public function regionalReviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'regional_reviewer_id');
    }

    public function nationalReviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'national_reviewer_id');
    }

    /**
     * Get all validation records (audit trail) for this prospection
     */
    public function validationRecords()
    {
        return $this->hasMany(ValidationRecord::class);
    }

    // === WORKFLOW DETERMINATION METHODS ===
    
    /**
     * Determine the prospector role type based on their user roles
     * Returns: 'superviseur', 'teacher', 'other'
     */
    public function determineProspectorRoleType(): string
    {
        $prospectorRoles = $this->prospector?->user?->getRoleNames() ?? collect();
        
        // Check if Superviseur
        if ($prospectorRoles->contains('Superviseur')) {
            return 'superviseur';
        }
        
        // Check if Teacher roles (Éducatrice, Animatrice, Facilitatrice)
        $teacherRoles = ['Éducatrice', 'Animatrice', 'Facilitatrice', 'Educatrice', 'Educateur/éducatrice'];
        foreach ($teacherRoles as $role) {
            if ($prospectorRoles->contains($role)) {
                return 'teacher';
            }
        }
        
        // Other roles
        return 'other';
    }

    /**
     * Get the next validation level based on current status and prospector type
     */
    public function getNextValidationLevel(): ?string
    {
        $currentStatus = $this->validation_status;
        $roleType = $this->prospector_role_type;
        
        // Mapping of status to next level
        // One pending_* at start, then only reviewed levels until validation
        $workflows = [
            // Superviseur workflow (3 levels)
            'superviseur' => [
                'draft' => 'pending_operational',
                'pending_operational' => 'operational_reviewed',
                'operational_reviewed' => 'regional_reviewed',
                'regional_reviewed' => 'validated',
            ],
            // Teacher workflow (4 levels)
            'teacher' => [
                'draft' => 'pending_supervisor',
                'pending_supervisor' => 'supervisor_reviewed',
                'supervisor_reviewed' => 'operational_reviewed',
                'operational_reviewed' => 'regional_reviewed',
                'regional_reviewed' => 'validated',
            ],
            // Other roles (direct to national)
            'other' => [
                'draft' => 'pending_national_direct',
                'pending_national_direct' => 'validated',
            ],
        ];
        
        return $workflows[$roleType][$currentStatus] ?? null;
    }

    /**
     * Check if prospection can move to next validation level
     */
    public function canTransitionToNext(): bool
    {
        return $this->getNextValidationLevel() !== null;
    }
}
