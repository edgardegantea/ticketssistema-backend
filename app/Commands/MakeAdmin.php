<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Shield\Models\UserModel;

class MakeAdmin extends BaseCommand
{
    protected $group       = 'shield';
    protected $name        = 'shield:make-admin';
    protected $description = 'Asigna el grupo admin a un usuario existente (Shield) buscándolo por email.';

    public function run(array $params)
    {
        $email = $params[0] ?? null;

        if (! $email) {
            $email = CLI::prompt('Email del usuario que quieres hacer admin');
        }

        // Usa directamente el UserModel de Shield
        $users = new UserModel();

        $user = $users
            ->where('email', $email)   // buscar por columna email
            ->first();

        if (! $user) {
            CLI::error("No se encontró un usuario con email: {$email}");
            return;
        }

        $user->addGroup('admin');     // método de la entidad User de Shield
        $users->save($user);

        CLI::write("Usuario {$email} ahora pertenece al grupo 'admin'.", 'green');
    }
}
