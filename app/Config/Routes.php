<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// Vue Routes
$routes->get('/', 'Vue::index');

// API Routes
$routes->group('api', ['namespace' => 'App\Controllers\Api'], function ($routes) {
    // Auth routes (no authentication required)
    $routes->post('register', 'AuthController::register');
    $routes->post('login', 'AuthController::login');
    $routes->get('coverage-types', 'CustomerController::getCoverageTypes'); // Move this outside the auth group

    // Protected routes (authentication required)
    $routes->group('', ['filter' => 'jwtauth'], function ($routes) {
        $routes->get('profile', 'AuthController::profile');
        $routes->post('logout', 'AuthController::logout');

        // Customer routes that need authentication
        $routes->get('customers', 'CustomerController::index');
        $routes->get('customers/(:num)', 'CustomerController::show/$1');
        $routes->post('customers', 'CustomerController::create');
        $routes->put('customers/(:num)', 'CustomerController::update/$1');
        $routes->delete('customers/(:num)', 'CustomerController::delete/$1');
    });
});

// Vue Router catch-all route
$routes->get('(:any)', 'Vue::index/$1');
