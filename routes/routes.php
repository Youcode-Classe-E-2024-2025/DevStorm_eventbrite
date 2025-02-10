<?php

use App\controllers\back\UserController;
use App\controllers\back\DashboardController;
use App\controllers\front\HomeController;
use App\Controllers\front\EventController;
use App\Controllers\back\OrganizerController;


use App\Core\Router;

$router = new Router();


$router->get('/', [HomeController::class, 'index']);
$router->get('/registerForm', [UserController::class, 'register']);
$router->get('/register', [UserController::class, 'register']);
$router->post('/register', [UserController::class, 'handleRegister']);
$router->get('/login', [UserController::class, 'login']);
$router->post('/login', [UserController::class, 'handleLogin']);
$router->get('/logout', [UserController::class, 'logout']);
$router->get('/dashboard', [DashboardController::class, 'Dashboard']);

//organizer ROUTEs
$router->get('/organizer/event/create', [OrganizerController::class, 'createEvent']);
$router->post('/organizer/event/create', [OrganizerController::class, 'handleCreateEvent']);
$router->get('/organizer/event/stats/{id}', [OrganizerController::class, 'salesStats']);
$router->get('/organizer/event/export/{id}', [OrganizerController::class, 'exportParticipants']);
$router->get('/organizer/dashboard', [OrganizerController::class, 'dashboard']);




$router->get('/event', [EventController::class, 'index']);

$router->dispatch();
