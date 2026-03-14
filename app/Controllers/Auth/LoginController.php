<?php

declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Shield\Authentication\Authenticators\Session;
use CodeIgniter\Shield\Authentication\JWTManager;
use CodeIgniter\Shield\Validation\ValidationRules;

class LoginController extends BaseController
{
    use ResponseTrait;

    /**
     * Login con email/user y password, devuelve JWT.
     */
    public function jwtLogin(): ResponseInterface
    {
        // Body como JSON
        $data = $this->request->getJSON(true);

        // Reglas de validación estándar de Shield
        $rules = (new ValidationRules())->getLoginRules();

        if (! $this->validateData($data, $rules, [], config('Auth')->DBGroup)) {
            return $this->fail(
                ['errors' => $this->validator->getErrors()],
                ResponseInterface::HTTP_UNAUTHORIZED
            );
        }

        // Campos válidos (email/username según tu config de Shield)
        $validFields = setting('Auth.validFields'); // por defecto ['email']

        $credentials = [];
        foreach ($validFields as $field) {
            $credentials[$field] = $data[$field] ?? null;
        }
        $credentials['password'] = $data['password'] ?? null;

        /** @var Session $authenticator */
        $authenticator = auth('session')->getAuthenticator();

        $result = $authenticator->check($credentials);

        if (! $result->isOK()) {
            return $this->failUnauthorized($result->reason());
        }

        $user = $result->extraInfo();

        /** @var JWTManager $manager */
        $manager = service('jwtmanager');

        // Claims personalizados (lo que quieras leer luego en React)
        $claims = [
            'email' => $user->email,
            'id'    => $user->id,
            // 'roles' => $user->getRoles(), // requiere AuthGroups config
        ];

        $jwt = $manager->generateToken($user, $claims);

        return $this->respond([
            'access_token' => $jwt,
            'token_type'   => 'Bearer',
            'user'         => [
                'id'    => $user->id,
                'email' => $user->email,
                // 'roles' => $user->getRoles(),
            ],
        ]);
    }

    /**
     * (Opcional) Endpoint para probar si el token es válido y ver el usuario.
     */
    public function me(): ResponseInterface
    {
        $user = auth()->user();

        if (! $user) {
            return $this->failUnauthorized('No autenticado');
        }

        return $this->respond([
            'id'    => $user->id,
            'email' => $user->email,
            'username' => $user->username,
            // 'roles' => $user->getRoles(),
        ]);
    }
}
