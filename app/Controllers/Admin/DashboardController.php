<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

class DashboardController extends BaseController
{
    use ResponseTrait;

    public function index(): ResponseInterface
    {
        $user = auth()->user();

        if (! $user) {
            return $this->failUnauthorized('No autenticado');
        }

        if (! $user->inGroup('admin')) {
            return $this->failForbidden('No tienes permisos para acceder al panel de administración');
        }

        return $this->respond([
            'message' => 'Bienvenido al dashboard admin',
            'user'    => [
                'id'       => $user->id,
                'username' => $user->username,
                'email'    => $user->email ?? null,
            ],
        ]);
    }
}
