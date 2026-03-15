<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

// Rutas de Shield base (login por formulario, etc.)
service('auth')->routes($routes);

$routes->group('api', static function ($routes) {
    $routes->get('auth/login', static function () {
        return 'GET api/auth/login OK (endpoint real es POST)';
    });

    $routes->post('auth/login', 'Auth\LoginController::jwtLogin');

    // /api/auth/me con filtro jwt
    $routes->get('auth/me', 'Auth\LoginController::me', ['filter' => 'jwt']);

    $routes->group('admin', ['filter' => ['jwt', 'group:admin']], static function ($routes) {
        $routes->get('dashboard', 'Admin\DashboardController::index');
        $routes->get('tickets', 'Admin\TicketController::index');
        $routes->get('tickets/summary', 'Admin\TicketController::summary');


        // IMPORTANTE: estas tres rutas para el CRUD
        $routes->post('tickets', 'Admin\TicketController::create');
        $routes->put('tickets/(:num)', 'Admin\TicketController::update/$1');
        $routes->patch('tickets/(:num)', 'Admin\TicketController::update/$1');
        $routes->delete('tickets/(:num)', 'Admin\TicketController::delete/$1');
        $routes->get('tickets/(:num)', 'Admin\TicketController::show/$1');

        $routes->patch('tickets/(:num)', 'Admin\TicketController::update/$1');
        $routes->put('tickets/(:num)', 'Admin\TicketController::update/$1');

        $routes->post('tickets/bulk-delete', 'Admin\TicketController::bulkDelete');


        $routes->get('tickets/(:num)/comments', 'Admin\TicketCommentController::index/$1');
        $routes->post('tickets/(:num)/comments', 'Admin\TicketCommentController::create/$1');

    });

    $routes->options('(:any)', static function () {
        $response = response();
        $response->setStatusCode(204);
        $response->setHeader('Allow', 'OPTIONS, GET, POST, PUT, PATCH, DELETE');
        return $response;
    });
});

