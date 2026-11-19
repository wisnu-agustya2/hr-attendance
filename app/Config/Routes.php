<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('login', 'AuthController::login');
$routes->post('login', 'AuthController::attempt');
$routes->post('logout', 'AuthController::logout');

$routes->group('', ['filter' => 'auth'], static function (RouteCollection $routes) {
    $routes->get('/', 'DashboardController::index');
    $routes->post('attendance/check-in', 'AttendanceController::checkIn');
    $routes->post('attendance/check-out', 'AttendanceController::checkOut');
    $routes->get('attendance/history', 'AttendanceController::history');

    $routes->get('leaves', 'LeaveController::index');
    $routes->get('leaves/new', 'LeaveController::new');
    $routes->post('leaves', 'LeaveController::create');
    $routes->post('leaves/(:num)/cancel', 'LeaveController::cancel/$1');

    $routes->group('admin', ['filter' => 'admin', 'namespace' => 'App\Controllers\Admin'], static function (RouteCollection $routes) {
        $routes->get('/', 'DashboardController::index');

        $routes->get('employees', 'EmployeeController::index');
        $routes->get('employees/new', 'EmployeeController::new');
        $routes->post('employees', 'EmployeeController::create');
        $routes->get('employees/(:num)/edit', 'EmployeeController::edit/$1');
        $routes->post('employees/(:num)', 'EmployeeController::update/$1');

        $routes->get('leaves', 'LeaveApprovalController::index');
        $routes->post('leaves/(:num)/approve', 'LeaveApprovalController::approve/$1');
        $routes->post('leaves/(:num)/reject', 'LeaveApprovalController::reject/$1');

        $routes->get('recap', 'RecapController::index');
        $routes->get('recap/export', 'RecapController::export');
    });
});
