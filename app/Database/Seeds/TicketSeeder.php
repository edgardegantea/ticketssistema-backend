<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class TicketSeeder extends Seeder
{
    public function run()
    {
        $now = Time::now('America/Mexico_City');

        $data = [
            [
                'titulo'      => 'Error al iniciar sesión',
                'descripcion' => 'El usuario no puede iniciar sesión con sus credenciales.',
                'estado'      => 'abierto',
                'prioridad'   => 'alta',
                'creado_por'  => 'user1@mail.com',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'titulo'      => 'Problema con envío de correo',
                'descripcion' => 'Los correos de confirmación no están llegando.',
                'estado'      => 'en progreso',
                'prioridad'   => 'media',
                'creado_por'  => 'user2@mail.com',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'titulo'      => 'Bug en formulario de soporte',
                'descripcion' => 'El formulario lanza error 500 al adjuntar archivos.',
                'estado'      => 'abierto',
                'prioridad'   => 'alta',
                'creado_por'  => 'user3@mail.com',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'titulo'      => 'Solicitud de nueva funcionalidad',
                'descripcion' => 'Agregar filtro por prioridad en el listado de tickets.',
                'estado'      => 'cerrado',
                'prioridad'   => 'baja',
                'creado_por'  => 'user4@mail.com',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
        ];

        $this->db->table('tickets')->insertBatch($data);
    }
}
