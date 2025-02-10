<?php

use App\Controllers\back\UserController;
use App\Controllers\back\DashboardController;

use App\Core\Router;

$router = new Router();


$router->get('/', [UserController::class, 'register']);
$router->get('/register', [UserController::class, 'register']);
$router->post('/register', [UserController::class, 'handleRegister']);
$router->get('/login', [UserController::class, 'login']);
$router->post('/login', [UserController::class, 'handleLogin']);
$router->get('/logout', [UserController::class, 'logout']);
$router->get('/dashboard', [DashboardController::class, 'Dashboard']);

$router->dispatch();
