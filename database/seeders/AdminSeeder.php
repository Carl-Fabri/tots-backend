<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear administrador por defecto
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrador',
                'email' => 'admin@example.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
            ]
        );

        // Crear usuarios de prueba adicionales
        User::firstOrCreate(
            ['email' => 'user1@example.com'],
            [
                'name' => 'Usuario Prueba 1',
                'email' => 'user1@example.com',
                'password' => Hash::make('password123'),
                'role' => 'user',
            ]
        );

        User::firstOrCreate(
            ['email' => 'user2@example.com'],
            [
                'name' => 'Usuario Prueba 2',
                'email' => 'user2@example.com',
                'password' => Hash::make('password123'),
                'role' => 'user',
            ]
        );

        $this->command->info('Usuarios administradores y de prueba creados exitosamente.');
        $this->command->info('Admin: admin@example.com / admin123');
        $this->command->info('Usuario 1: user1@example.com / password123');
        $this->command->info('Usuario 2: user2@example.com / password123');
    }
}

