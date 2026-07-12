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

$routes->get('/attendances', 'AttendanceController::index', ['filter' => 'auth']);
$routes->get('/attendances/create', 'AttendanceController::create', ['filter' => 'auth']);
$routes->post('/attendances/store', 'AttendanceController::store', ['filter' => 'auth']);
$routes->get('/attendances/edit/(:num)', 'AttendanceController::edit/$1', ['filter' => 'auth']);
$routes->post('/attendances/update/(:num)', 'AttendanceController::update/$1', ['filter' => 'auth']);
$routes->get('/attendances/delete/(:num)', 'AttendanceController::delete/$1', ['filter' => 'auth']);
$routes->get('/attendances/recap/(:num)', 'AttendanceController::recap/$1', ['filter' => 'auth']);
$routes->get('/attendances/recap/(:num)/print', 'AttendanceController::recapPrint/$1', ['filter' => 'auth']);

$routes->get('/cash', 'CashTransactionController::index', ['filter' => 'auth']);
$routes->get('/cash/create', 'CashTransactionController::create', ['filter' => 'auth']);
$routes->post('/cash/store', 'CashTransactionController::store', ['filter' => 'auth']);
$routes->get('/cash/edit/(:num)', 'CashTransactionController::edit/$1', ['filter' => 'auth']);
$routes->post('/cash/update/(:num)', 'CashTransactionController::update/$1', ['filter' => 'auth']);
$routes->get('/cash/delete/(:num)', 'CashTransactionController::delete/$1', ['filter' => 'auth']);

$routes->get('/activities', 'ActivityController::index', ['filter' => 'auth']);
$routes->get('/activities/create', 'ActivityController::create', ['filter' => 'auth']);
$routes->post('/activities/store', 'ActivityController::store', ['filter' => 'auth']);
$routes->get('/activities/edit/(:num)', 'ActivityController::edit/$1', ['filter' => 'auth']);
$routes->post('/activities/update/(:num)', 'ActivityController::update/$1', ['filter' => 'auth']);
$routes->get('/activities/delete/(:num)', 'ActivityController::delete/$1', ['filter' => 'auth']);

$routes->get('/reports', 'ReportController::index', ['filter' => 'auth']);
$routes->get('/reports/members', 'ReportController::members', ['filter' => 'auth']);
$routes->get('/reports/cash', 'ReportController::cash', ['filter' => 'auth']);
$routes->get('/reports/meetings', 'ReportController::meetings', ['filter' => 'auth']);