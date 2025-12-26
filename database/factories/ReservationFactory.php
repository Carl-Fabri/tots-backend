<?php

namespace Database\Factories;

use App\Models\Reservation;
use App\Models\Space;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reservation>
 */
class ReservationFactory extends Factory
{
    protected $model = Reservation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startTime = Carbon::now()->addDays($this->faker->numberBetween(1, 30))
            ->setTime($this->faker->numberBetween(8, 17), $this->faker->randomElement([0, 30]));

        $endTime = $startTime->copy()->addHours($this->faker->numberBetween(1, 3));

        return [
            'user_id' => User::factory(),
            'space_id' => Space::factory(),
            'title' => $this->faker->randomElement([
                'Reunión de Equipo',
                'Presentación de Proyecto',
                'Capacitación',
                'Revisión de Código',
                'Planificación de Sprint',
                'Reunión con Cliente',
            ]),
            'description' => $this->faker->optional()->sentence(),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'cancelled']),
        ];
    }
}

