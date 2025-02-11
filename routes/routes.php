<?php

use App\controllers\back\UserController;
use App\controllers\back\DashboardController;
use App\controllers\front\HomeController;
use App\controllers\front\EventController;
use App\controllers\back\OrganizerController;


use App\Core\Router;

$router = new Router();


$router->get('/', [HomeController::class, 'index']);
$router->get('/login', [UserController::class, 'login']);
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
$router->get('/event/{id}/stats', [OrganizerController::class, 'eventStats']);

//promocode
$router->get('/event/{id}/promocode/create', [OrganizerController::class, 'createPromoCode']);
$router->post('/event/{id}/promocode/create', [OrganizerController::class, 'createPromoCode']);
$router->get('/event/{id}/promocodes', [OrganizerController::class, 'listPromoCodes']);





$router->get('/event', [EventController::class, 'index']);

$router->dispatch();
