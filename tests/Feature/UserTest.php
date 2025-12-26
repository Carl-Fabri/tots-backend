<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
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

    public function test_admin_can_list_all_users()
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'users' => [
                        '*' => ['id', 'name', 'email', 'role']
                    ]
                ]
            ]);
    }

    public function test_regular_user_cannot_list_all_users()
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->userToken}")
            ->getJson('/api/users');

        $response->assertStatus(403);
    }

    public function test_admin_can_create_user()
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson('/api/users', [
                'name' => 'New User',
                'email' => 'newuser@example.com',
                'password' => 'password123',
                'role' => 'user',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'role' => 'user',
        ]);
    }

    public function test_admin_can_update_user()
    {
        $user = User::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->putJson("/api/users/{$user->id}", [
                'name' => 'Updated Name',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_user_can_view_own_profile()
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->userToken}")
            ->getJson("/api/users/{$this->user->id}");

        $response->assertStatus(200);
    }

    public function test_user_cannot_view_other_user_profile()
    {
        $otherUser = User::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer {$this->userToken}")
            ->getJson("/api/users/{$otherUser->id}");

        $response->assertStatus(403);
    }

    public function test_admin_can_delete_user()
    {
        $user = User::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->deleteJson("/api/users/{$user->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}

