<?php

namespace Tests\Feature;

use App\Models\Space;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpaceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->user = User::factory()->create(['role' => 'user']);
        $this->adminToken = auth()->login($this->admin);
        $this->userToken = auth()->login($this->user);
    }

    public function test_authenticated_user_can_list_spaces()
    {
        Space::factory()->count(3)->create();

        $response = $this->withHeader('Authorization', "Bearer {$this->userToken}")
            ->getJson('/api/spaces');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'spaces' => [
                        '*' => ['id', 'name', 'capacity', 'is_active']
                    ]
                ]
            ]);
    }

    public function test_admin_can_create_space()
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson('/api/spaces', [
                'name' => 'Test Space',
                'description' => 'Test Description',
                'capacity' => 20,
                'location' => 'Test Location',
                'is_active' => true,
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('spaces', [
            'name' => 'Test Space',
            'capacity' => 20,
        ]);
    }

    public function test_regular_user_cannot_create_space()
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->userToken}")
            ->postJson('/api/spaces', [
                'name' => 'Test Space',
                'capacity' => 20,
            ]);

        $response->assertStatus(403);
    }

    public function test_authenticated_user_can_view_space()
    {
        $space = Space::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer {$this->userToken}")
            ->getJson("/api/spaces/{$space->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'space' => [
                        'id' => $space->id,
                        'name' => $space->name,
                    ]
                ]
            ]);
    }

    public function test_admin_can_update_space()
    {
        $space = Space::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->putJson("/api/spaces/{$space->id}", [
                'name' => 'Updated Space Name',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('spaces', [
            'id' => $space->id,
            'name' => 'Updated Space Name',
        ]);
    }

    public function test_admin_can_delete_space()
    {
        $space = Space::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->deleteJson("/api/spaces/{$space->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('spaces', ['id' => $space->id]);
    }
}

