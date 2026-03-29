<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ProgramResource;
use App\Http\Resources\SiteResource;

class ProspectionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return  [
            'id' => $this->id,
            'prospections_id' => $this->prospections_id,

            // --- Foreign Keys ---
            'program_id' => $this->program_id,
            'program' => ProgramResource::make($this->whenLoaded('program')),
            'site_id' => $this->site_id,
            'site' => SiteResource::make($this->whenLoaded('site')),
            'prospector_id' => $this->prospector_id,
            'prospector' => $this->whenLoaded('prospector', fn() => $this->prospector ? $this->prospector->first_name . ' ' . $this->prospector->last_name : null),

            // --- Identification ---
            'title' => $this->title,

            // --- Zone ---
            'douar_access' => $this->douar_access,
            'distance_to_nearest_douar_km' => $this->distance_to_nearest_douar_km,
            'distance_to_nearest_city_km' => $this->distance_to_nearest_city_km,
            'main_language' => $this->main_language,

            // --- Population characteristics ---
            'households' => $this->households,
            'total_population' => $this->total_population,
            'literacy_rate' => $this->literacy_rate,
            'schooling_rate' => $this->schooling_rate,

            // Children
            'children_0_5_b' => $this->children_0_5_b,
            'children_0_5_g' => $this->children_0_5_g,
            'children_6_12_b' => $this->children_6_12_b,
            'children_6_12_g' => $this->children_6_12_g,
            'children_13_18_b' => $this->children_13_18_b,
            'children_13_18_g' => $this->children_13_18_g,

            // Youth
            'youth_m' => $this->youth_m,
            'youth_f' => $this->youth_f,

            // Adults
            'adults_m' => $this->adults_m,
            'adults_f' => $this->adults_f,
            'remarque' => $this->remarque,
              
            // --- Access to services ---
            'preschool' => $this->preschool,
            'preschool_count' => $this->preschool_count,

            'primary_school' => $this->primary_school,
            'primary_school_count' => $this->primary_school_count,

            'middle_school' => $this->middle_school,
            'middle_school_count' => $this->middle_school_count,

            'high_school' => $this->high_school,
            'high_school_count' => $this->high_school_count,

            'second_chance_center' => $this->second_chance_center,
            'second_chance_center_count' => $this->second_chance_center_count,

            'health_center' => $this->health_center,
            'health_center_count' => $this->health_center_count,

            'pharmacy' => $this->pharmacy,
            'pharmacy_count' => $this->pharmacy_count,

            'training_center' => $this->training_center,
            'training_center_count' => $this->training_center_count,

            'literacy_center' => $this->literacy_center,
            'literacy_center_count' => $this->literacy_center_count,

            'guidance_center' => $this->guidance_center,
            'guidance_center_count' => $this->guidance_center_count,

            'youth_house' => $this->youth_house,
            'youth_house_count' => $this->youth_house_count,

            'active_associations' => $this->active_associations,
            'association_names' => $this->association_names,
            'association_activity_type' => $this->association_activity_type,
            'association_members_count' => $this->association_members_count,
            'association_contact' => $this->association_contact,

            'educational_clubs' => $this->educational_clubs,
            'educational_clubs_count' => $this->educational_clubs_count,

            'sports_space' => $this->sports_space,
            'sports_space_count' => $this->sports_space_count,

            'school_transport' => $this->school_transport,
            'transport_provider' => $this->transport_provider,

            'student_accommodation' => $this->student_accommodation,
            'student_accommodation_count' => $this->student_accommodation_count,

            'economic_activities' => $this->economic_activities,
            'cultural_activities' => $this->cultural_activities,

            // --- Local Prospected ---
            'local_name' => $this->local_name,
            'owner_type' => $this->owner_type,
            'owner_status' => $this->owner_status,
            'manager_status' => $this->manager_status,
            'manager_structure' => $this->manager_structure,

            'surface_area' => $this->surface_area,
            'available_rooms_count' => $this->available_rooms_count,
            'training_rooms_count' => $this->training_rooms_count,
            'management_rooms_count' => $this->management_rooms_count,
            'sanitary_blocks_count' => $this->sanitary_blocks_count,

            'water_connection' => $this->water_connection,
            'electricity_connection' => $this->electricity_connection,
            'internet_access' => $this->internet_access,
            'security' => $this->security,

            'needs_renovation' => $this->needs_renovation,
            'renovation_type' => $this->renovation_type,

            // --- Equipment ---
            'has_equipment' => $this->has_equipment,

            'individual_tables_count' => $this->individual_tables_count,
            'individual_tables_condition' => $this->individual_tables_condition,

            'collective_tables_count' => $this->collective_tables_count,
            'collective_tables_condition' => $this->collective_tables_condition,

            'boards_count' => $this->boards_count,
            'boards_condition' => $this->boards_condition,

            'display_boards_count' => $this->display_boards_count,
            'display_boards_condition' => $this->display_boards_condition,

            'coat_hangers_count' => $this->coat_hangers_count,
            'coat_hangers_condition' => $this->coat_hangers_condition,

            'teacher_desks_count' => $this->teacher_desks_count,
            'teacher_desks_condition' => $this->teacher_desks_condition,

            'teacher_chairs_count' => $this->teacher_chairs_count,
            'teacher_chairs_condition' => $this->teacher_chairs_condition,

            'children_chairs_count' => $this->children_chairs_count,
            'children_chairs_condition' => $this->children_chairs_condition,

            'student_chairs_count' => $this->student_chairs_count,
            'student_chairs_condition' => $this->student_chairs_condition,

            'cabinets_count' => $this->cabinets_count,
            'cabinets_condition' => $this->cabinets_condition,

            'storage_wardrobes_count' => $this->storage_wardrobes_count,
            'storage_wardrobes_condition' => $this->storage_wardrobes_condition,

            'libraries_count' => $this->libraries_count,
            'libraries_condition' => $this->libraries_condition,

            'extinguishers_count' => $this->extinguishers_count,
            'extinguishers_condition' => $this->extinguishers_condition,

            'desktop_computers_count' => $this->desktop_computers_count,
            'desktop_computers_condition' => $this->desktop_computers_condition,

            'laptops_count' => $this->laptops_count,
            'laptops_condition' => $this->laptops_condition,

            'printers_count' => $this->printers_count,
            'printers_condition' => $this->printers_condition,

            'copiers_count' => $this->copiers_count,
            'copiers_condition' => $this->copiers_condition,

            'video_projectors_count' => $this->video_projectors_count,
            'video_projectors_condition' => $this->video_projectors_condition,

            'projection_screens_count' => $this->projection_screens_count,
            'projection_screens_condition' => $this->projection_screens_condition,

            'interactive_boards_count' => $this->interactive_boards_count,
            'interactive_boards_condition' => $this->interactive_boards_condition,

            'other_equipment_count' => $this->other_equipment_count,
            'other_equipment_condition' => $this->other_equipment_condition,
            'other_equipment_description' => $this->other_equipment_description,

            // Outdoor spaces
            'has_outdoor_spaces' => $this->has_outdoor_spaces,

            // Other local
            'other_local_to_prospect' => $this->other_local_to_prospect,

            // Observations
            'equipment_observations' => $this->equipment_observations,

            // Decision
            'decision' => $this->decision,

            // Projects (JSON)
            'potential_projects' => $this->potential_projects,

            'final_observations' => $this->final_observations,

            // === VALIDATION WORKFLOW ===
            'validation_status' => $this->validation_status,
            'prospector_role_type' => $this->prospector_role_type,
            
            // Observations by level
            'supervisor_observations' => $this->supervisor_observations,
            'operational_observations' => $this->operational_observations,
            'regional_observations' => $this->regional_observations,
            'national_observations' => $this->national_observations,
            
            // Reviewer information
            'supervisor_reviewer' => $this->whenLoaded('supervisorReviewer', fn() => [
                'id' => $this->supervisor_reviewer_id,
                'name' => $this->supervisorReviewer?->name,
                'reviewed_at' => $this->supervisor_reviewed_at?->toDateTimeString(),
            ]),
            'operational_reviewer' => $this->whenLoaded('operationalReviewer', fn() => [
                'id' => $this->operational_reviewer_id,
                'name' => $this->operationalReviewer?->name,
                'reviewed_at' => $this->operational_reviewed_at?->toDateTimeString(),
            ]),
            'regional_reviewer' => $this->whenLoaded('regionalReviewer', fn() => [
                'id' => $this->regional_reviewer_id,
                'name' => $this->regionalReviewer?->name,
                'reviewed_at' => $this->regional_reviewed_at?->toDateTimeString(),
            ]),
            'national_reviewer' => $this->whenLoaded('nationalReviewer', fn() => [
                'id' => $this->national_reviewer_id,
                'name' => $this->nationalReviewer?->name,
                'reviewed_at' => $this->national_reviewed_at?->toDateTimeString(),
            ]),
            
            // Rejection tracking
            'is_rejected' => $this->is_rejected,
            'rejected_at' => $this->rejected_at?->toDateTimeString(),
            'rejection_reason' => $this->rejection_reason,

            // Timestamps
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            'deleted_at' => $this->deleted_at?->toDateTimeString(),
        ];
    }
}
