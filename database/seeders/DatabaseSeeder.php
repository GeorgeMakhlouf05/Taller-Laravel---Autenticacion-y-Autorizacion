<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Puebla la base de datos con los datos de prueba del taller.
     *
     * Credenciales de prueba (contraseña de todos: "password"):
     *  - admin@eventhub.com        (rol: admin)
     *  - organizador.a@eventhub.com (rol: organizador)
     *  - organizador.b@eventhub.com (rol: organizador)
     *  - asistente@eventhub.com    (rol: asistente)
     */
    public function run(): void
    {
        // Un usuario con rol admin
        User::updateOrCreate(
            ['email' => 'admin@eventhub.com'],
            ['name' => 'Administrador Global', 'password' => 'password', 'role' => 'admin']
        );

        // Dos usuarios con rol organizador
        $organizadorA = User::updateOrCreate(
            ['email' => 'organizador.a@eventhub.com'],
            ['name' => 'Organizador A', 'password' => 'password', 'role' => 'organizador']
        );

        User::updateOrCreate(
            ['email' => 'organizador.b@eventhub.com'],
            ['name' => 'Organizador B', 'password' => 'password', 'role' => 'organizador']
        );

        // Un usuario con rol asistente
        User::updateOrCreate(
            ['email' => 'asistente@eventhub.com'],
            ['name' => 'Asistente de Prueba', 'password' => 'password', 'role' => 'asistente']
        );

        // Al menos un evento a nombre del Organizador A.
        // Al crearlo desde la relación eventos(), Laravel asigna user_id automáticamente.
        $organizadorA->eventos()->updateOrCreate(
            ['titulo' => 'Conferencia de Desarrollo Backend'],
            [
                'descripcion' => 'Conferencia sobre APIs RESTful, autenticación y autorización con Laravel.',
                'cupo_maximo' => 100,
            ]
        );
    }
}