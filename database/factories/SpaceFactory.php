<?php

namespace Database\Factories;

use App\Models\Space;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Space>
 */
class SpaceFactory extends Factory
{
    protected $model = Space::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true) . ' ' . $this->faker->randomElement(['Sala', 'Aula', 'Auditorio', 'Oficina']),
            'description' => $this->faker->sentence(),
            'capacity' => $this->faker->numberBetween(5, 100),
            'location' => 'Piso ' . $this->faker->numberBetween(1, 5) . ', Edificio ' . $this->faker->randomElement(['Principal', 'Secundario', 'Anexo']),
            'is_active' => $this->faker->boolean(90),
        ];
    }
}

