<?php

namespace Database\Seeders;

use App\Models\Reservation;
use App\Models\Space;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ReservationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('role', 'user')->get();
        $spaces = Space::all();

        if ($users->isEmpty() || $spaces->isEmpty()) {
            $this->command->warn('No hay usuarios o espacios disponibles. Ejecuta primero AdminSeeder y SpaceSeeder.');
            return;
        }

        // Crear algunas reservas de ejemplo para los próximos días
        $today = Carbon::today();
        
        // Reservas para hoy
        Reservation::create([
            'user_id' => $users->first()->id,
            'space_id' => $spaces->first()->id,
            'title' => 'Reunión de Equipo',
            'description' => 'Reunión semanal del equipo de desarrollo',
            'start_time' => $today->copy()->setTime(10, 0),
            'end_time' => $today->copy()->setTime(11, 30),
            'status' => 'confirmed',
        ]);

        Reservation::create([
            'user_id' => $users->first()->id,
            'space_id' => $spaces->skip(1)->first()->id,
            'title' => 'Presentación de Proyecto',
            'description' => 'Presentación del nuevo proyecto al cliente',
            'start_time' => $today->copy()->setTime(14, 0),
            'end_time' => $today->copy()->setTime(15, 30),
            'status' => 'confirmed',
        ]);

        // Reservas para mañana
        $tomorrow = $today->copy()->addDay();
        
        Reservation::create([
            'user_id' => $users->skip(1)->first()->id ?? $users->first()->id,
            'space_id' => $spaces->first()->id,
            'title' => 'Capacitación de Nuevos Empleados',
            'description' => 'Sesión de capacitación para nuevos miembros del equipo',
            'start_time' => $tomorrow->copy()->setTime(9, 0),
            'end_time' => $tomorrow->copy()->setTime(12, 0),
            'status' => 'confirmed',
        ]);

        Reservation::create([
            'user_id' => $users->first()->id,
            'space_id' => $spaces->skip(2)->first()->id,
            'title' => 'Reunión de Planificación',
            'description' => 'Planificación del próximo sprint',
            'start_time' => $tomorrow->copy()->setTime(15, 0),
            'end_time' => $tomorrow->copy()->setTime(16, 30),
            'status' => 'confirmed',
        ]);

        // Reservas para la próxima semana
        $nextWeek = $today->copy()->addWeek();
        
        Reservation::create([
            'user_id' => $users->first()->id,
            'space_id' => $spaces->first()->id,
            'title' => 'Revisión de Código',
            'description' => 'Sesión de revisión de código en equipo',
            'start_time' => $nextWeek->copy()->setTime(10, 0),
            'end_time' => $nextWeek->copy()->setTime(11, 0),
            'status' => 'confirmed',
        ]);

        $this->command->info('Reservas de ejemplo creadas exitosamente.');
    }
}

