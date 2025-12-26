<?php

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\Space;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->user = User::factory()->create(['role' => 'user']);
        $this->otherUser = User::factory()->create(['role' => 'user']);
        $this->adminToken = auth()->login($this->admin);
        $this->userToken = auth()->login($this->user);
        $this->space = Space::factory()->create(['is_active' => true]);
    }

    public function test_user_can_create_reservation()
    {
        $startTime = Carbon::now()->addDay()->setTime(10, 0);
        $endTime = Carbon::now()->addDay()->setTime(11, 0);

        $response = $this->withHeader('Authorization', "Bearer {$this->userToken}")
            ->postJson('/api/reservations', [
                'space_id' => $this->space->id,
                'title' => 'Test Reservation',
                'description' => 'Test Description',
                'start_time' => $startTime->toDateTimeString(),
                'end_time' => $endTime->toDateTimeString(),
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('reservations', [
            'user_id' => $this->user->id,
            'space_id' => $this->space->id,
            'title' => 'Test Reservation',
        ]);
    }

    public function test_user_cannot_create_overlapping_reservation()
    {
        $startTime = Carbon::now()->addDay()->setTime(10, 0);
        $endTime = Carbon::now()->addDay()->setTime(11, 0);

        // Crear primera reserva
        Reservation::create([
            'user_id' => $this->otherUser->id,
            'space_id' => $this->space->id,
            'title' => 'Existing Reservation',
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => 'confirmed',
        ]);

        // Intentar crear reserva superpuesta
        $response = $this->withHeader('Authorization', "Bearer {$this->userToken}")
            ->postJson('/api/reservations', [
                'space_id' => $this->space->id,
                'title' => 'Overlapping Reservation',
                'start_time' => $startTime->copy()->addMinutes(30)->toDateTimeString(),
                'end_time' => $endTime->copy()->addMinutes(30)->toDateTimeString(),
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'status' => 'error',
                'message' => 'Ya existe una reserva en este horario para este espacio',
            ]);
    }

    public function test_user_can_list_own_reservations()
    {
        Reservation::factory()->create([
            'user_id' => $this->user->id,
            'space_id' => $this->space->id,
        ]);

        Reservation::factory()->create([
            'user_id' => $this->otherUser->id,
            'space_id' => $this->space->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->userToken}")
            ->getJson('/api/reservations');

        $response->assertStatus(200);
        $reservations = $response->json('data.reservations');
        $this->assertCount(1, $reservations);
        $this->assertEquals($this->user->id, $reservations[0]['user_id']);
    }

    public function test_user_can_view_own_reservation()
    {
        $reservation = Reservation::factory()->create([
            'user_id' => $this->user->id,
            'space_id' => $this->space->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->userToken}")
            ->getJson("/api/reservations/{$reservation->id}");

        $response->assertStatus(200);
    }

    public function test_user_cannot_view_other_user_reservation()
    {
        $reservation = Reservation::factory()->create([
            'user_id' => $this->otherUser->id,
            'space_id' => $this->space->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->userToken}")
            ->getJson("/api/reservations/{$reservation->id}");

        $response->assertStatus(403);
    }

    public function test_user_can_update_own_reservation()
    {
        $startTime = Carbon::now()->addDays(2)->setTime(10, 0);
        $endTime = Carbon::now()->addDays(2)->setTime(11, 0);

        $reservation = Reservation::factory()->create([
            'user_id' => $this->user->id,
            'space_id' => $this->space->id,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->userToken}")
            ->putJson("/api/reservations/{$reservation->id}", [
                'title' => 'Updated Title',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_user_can_delete_own_reservation()
    {
        $reservation = Reservation::factory()->create([
            'user_id' => $this->user->id,
            'space_id' => $this->space->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->userToken}")
            ->deleteJson("/api/reservations/{$reservation->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('reservations', ['id' => $reservation->id]);
    }

    public function test_calendar_endpoint_returns_reservations_in_date_range()
    {
        $startDate = Carbon::now()->addDay();
        $endDate = Carbon::now()->addDays(3);

        Reservation::factory()->create([
            'space_id' => $this->space->id,
            'start_time' => $startDate->copy()->setTime(10, 0),
            'end_time' => $startDate->copy()->setTime(11, 0),
            'status' => 'confirmed',
        ]);

        Reservation::factory()->create([
            'space_id' => $this->space->id,
            'start_time' => Carbon::now()->addDays(5)->setTime(10, 0),
            'end_time' => Carbon::now()->addDays(5)->setTime(11, 0),
            'status' => 'confirmed',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->userToken}")
            ->getJson('/api/reservations/calendar', [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ]);

        $response->assertStatus(200);
        $reservations = $response->json('data.reservations');
        $this->assertCount(1, $reservations);
    }
}

