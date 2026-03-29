<?php

namespace Tests\Feature;

use App\Enums\ClassStateEnum;
use App\Enums\ClassStatusValueEnum;
use App\Models\ClassStatusHistory;
use App\Models\Project;
use App\Models\ProjectClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassOperationalTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Project $project;
    protected Project $targetProject;
    protected ProjectClass $class;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->project = Project::factory()->create();
        $this->targetProject = Project::factory()->create();
        
        $this->class = ProjectClass::factory()
            ->for($this->user)
            ->create([
                'class_state' => 'Création',
                'class_status_value' => 'Opérationnel',
            ]);
        
        $this->actingAs($this->user);
    }

    /**
     * Test get operational options
     */
    public function test_get_operational_options(): void
    {
        $response = $this->getJson('/api/class/operational-tracking/options');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'states' => [
                    '*' => ['value', 'label']
                ],
                'statuses' => [
                    '*' => ['value', 'label']
                ]
            ]);

        $this->assertTrue(
            collect($response->json('states'))->pluck('value')->contains('Transfert')
        );
    }

    /**
     * Test update class state to transfer
     */
    public function test_update_class_state_to_transfer(): void
    {
        $response = $this->putJson("/api/class/{$this->class->id}/state", [
            'class_state' => 'Transfert',
            'transfer_to_project_id' => $this->targetProject->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Class state updated successfully.',
            ]);

        $this->assertDatabaseHas('class', [
            'id' => $this->class->id,
            'class_state' => 'Transfert',
            'transfer_to_project_id' => $this->targetProject->id,
        ]);

        // Check history was created
        $this->assertDatabaseHas('class_status_histories', [
            'class_id' => $this->class->id,
            'change_type' => 'state',
            'old_value' => 'Création',
            'new_value' => 'Transfert',
        ]);
    }

    /**
     * Test update class state to relocation
     */
    public function test_update_class_state_to_relocation(): void
    {
        $targetClass = ProjectClass::factory()->for($this->user)->create();

        $response = $this->putJson("/api/class/{$this->class->id}/state", [
            'class_state' => 'Relocalisation',
            'relocate_to_class_id' => $targetClass->id,
            'relocation_date' => '2025-12-31',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('class', [
            'id' => $this->class->id,
            'class_state' => 'Relocalisation',
            'relocate_to_class_id' => $targetClass->id,
            'relocation_date' => '2025-12-31',
        ]);
    }

    /**
     * Test update class status
     */
    public function test_update_class_status(): void
    {
        $response = $this->putJson("/api/class/{$this->class->id}/status", [
            'class_status_value' => 'En arrêt',
            'status_change_date' => '2025-12-30',
            'status_change_reason' => 'Manque de ressources',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Class status updated successfully.',
            ]);

        $this->assertDatabaseHas('class', [
            'id' => $this->class->id,
            'class_status_value' => 'En arrêt',
            'status_change_reason' => 'Manque de ressources',
        ]);

        $this->assertDatabaseHas('class_status_histories', [
            'class_id' => $this->class->id,
            'change_type' => 'status',
            'old_value' => 'Opérationnel',
            'new_value' => 'En arrêt',
            'reason' => 'Manque de ressources',
        ]);
    }

    /**
     * Test update class status to perpetuated
     */
    public function test_update_class_status_to_perpetuated(): void
    {
        $response = $this->putJson("/api/class/{$this->class->id}/status", [
            'class_status_value' => 'Pérennisé',
            'status_change_date' => '2025-12-30',
            'perpetuation_project_id' => $this->targetProject->id,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('class', [
            'id' => $this->class->id,
            'class_status_value' => 'Pérennisé',
            'perpetuation_project_id' => $this->targetProject->id,
        ]);
    }

    /**
     * Test get status history
     */
    public function test_get_status_history(): void
    {
        // Make some changes
        $this->putJson("/api/class/{$this->class->id}/state", [
            'class_state' => 'Transfert',
            'transfer_to_project_id' => $this->targetProject->id,
        ]);

        $this->putJson("/api/class/{$this->class->id}/status", [
            'class_status_value' => 'En arrêt',
            'status_change_reason' => 'Motif test',
        ]);

        $response = $this->getJson("/api/class/{$this->class->id}/status-history");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'class_id',
                        'change_type',
                        'old_value',
                        'new_value',
                        'change_date',
                        'user',
                        'created_at',
                    ]
                ],
                'count'
            ]);

        $this->assertGreaterThanOrEqual(2, $response->json('count'));
    }

    /**
     * Test validation errors for state update
     */
    public function test_state_update_validation_error(): void
    {
        $response = $this->putJson("/api/class/{$this->class->id}/state", [
            'class_state' => 'InvalidState',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('class_state');
    }

    /**
     * Test validation errors for status update
     */
    public function test_status_update_validation_error(): void
    {
        $response = $this->putJson("/api/class/{$this->class->id}/status", [
            'class_status_value' => 'InvalidStatus',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('class_status_value');
    }

    /**
     * Test transfer without target project
     */
    public function test_transfer_without_target_project_fails(): void
    {
        $response = $this->putJson("/api/class/{$this->class->id}/state", [
            'class_state' => 'Transfert',
        ]);

        $response->assertStatus(500);
    }

    /**
     * Test perpetuation without target project
     */
    public function test_perpetuation_without_target_project_fails(): void
    {
        $response = $this->putJson("/api/class/{$this->class->id}/status", [
            'class_status_value' => 'Pérennisé',
        ]);

        $response->assertStatus(500);
    }
}
