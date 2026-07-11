<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');
$routes->get('/', 'DashboardController::index', ['filter' => 'auth']);

$routes->get('/login', 'AuthController::login');
$routes->post('/login', 'AuthController::attemptLogin');
$routes->get('/logout', 'AuthController::logout');

$routes->get('/dashboard', 'DashboardController::index', ['filter' => 'auth']);

$routes->get('/members', 'MemberController::index', ['filter' => 'auth']);
$routes->get('/members/create', 'MemberController::create', ['filter' => 'auth']);
$routes->post('/members/store', 'MemberController::store', ['filter' => 'auth']);
$routes->get('/members/edit/(:num)', 'MemberController::edit/$1', ['filter' => 'auth']);
$routes->post('/members/update/(:num)', 'MemberController::update/$1', ['filter' => 'auth']);
$routes->get('/members/delete/(:num)', 'MemberController::delete/$1', ['filter' => 'auth']);

$routes->get('/structures', 'StructureController::index', ['filter' => 'auth']);
$routes->get('/structures/create', 'StructureController::create', ['filter' => 'auth']);
$routes->post('/structures/store', 'StructureController::store', ['filter' => 'auth']);
$routes->get('/structures/edit/(:num)', 'StructureController::edit/$1', ['filter' => 'auth']);
$routes->post('/structures/update/(:num)', 'StructureController::update/$1', ['filter' => 'auth']);
$routes->get('/structures/delete/(:num)', 'StructureController::delete/$1', ['filter' => 'auth']);

$routes->get('/meetings', 'MeetingController::index', ['filter' => 'auth']);
$routes->get('/meetings/create', 'MeetingController::create', ['filter' => 'auth']);
$routes->post('/meetings/store', 'MeetingController::store', ['filter' => 'auth']);
$routes->get('/meetings/edit/(:num)', 'MeetingController::edit/$1', ['filter' => 'auth']);
$routes->post('/meetings/update/(:num)', 'MeetingController::update/$1', ['filter' => 'auth']);
$routes->get('/meetings/delete/(:num)', 'MeetingController::delete/$1', ['filter' => 'auth']);