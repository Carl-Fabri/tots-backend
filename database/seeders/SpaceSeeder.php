<?php

namespace Database\Seeders;

use App\Models\Space;
use Illuminate\Database\Seeder;

class SpaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $spaces = [
            [
                'name' => 'Sala de Conferencias A',
                'description' => 'Sala equipada con proyector, pizarra y capacidad para 20 personas. Ideal para reuniones de equipo.',
                'capacity' => 20,
                'location' => 'Piso 3, Edificio Principal',
                'is_active' => true,
            ],
            [
                'name' => 'Sala de Conferencias B',
                'description' => 'Sala más pequeña con capacidad para 10 personas. Equipada con TV y sistema de videoconferencia.',
                'capacity' => 10,
                'location' => 'Piso 3, Edificio Principal',
                'is_active' => true,
            ],
            [
                'name' => 'Sala de Reuniones C',
                'description' => 'Sala informal con sofás y mesas. Perfecta para reuniones casuales o brainstorming.',
                'capacity' => 8,
                'location' => 'Piso 2, Edificio Principal',
                'is_active' => true,
            ],
            [
                'name' => 'Auditorio Principal',
                'description' => 'Auditorio grande con capacidad para 100 personas. Equipado con sistema de sonido y proyección.',
                'capacity' => 100,
                'location' => 'Piso 1, Edificio Principal',
                'is_active' => true,
            ],
            [
                'name' => 'Sala de Capacitación',
                'description' => 'Sala diseñada para capacitaciones con mesas y sillas organizadas en formato de aula.',
                'capacity' => 30,
                'location' => 'Piso 2, Edificio Secundario',
                'is_active' => true,
            ],
            [
                'name' => 'Sala de Juntas Ejecutivas',
                'description' => 'Sala exclusiva para juntas ejecutivas con mobiliario de lujo.',
                'capacity' => 12,
                'location' => 'Piso 4, Edificio Principal',
                'is_active' => true,
            ],
        ];

        foreach ($spaces as $space) {
            Space::firstOrCreate(
                ['name' => $space['name']],
                $space
            );
        }

        $this->command->info('Espacios creados exitosamente.');
    }
}

