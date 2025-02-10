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






$router->get('/event', [EventController::class, 'index']);

$router->dispatch();
