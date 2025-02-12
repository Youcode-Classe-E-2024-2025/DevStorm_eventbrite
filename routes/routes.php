<?php

use App\controllers\back\UserController;
use App\controllers\back\DashboardController;
use App\controllers\front\HomeController;
use App\controllers\front\EventController;
use App\controllers\back\OrganizerController;
use App\controllers\front\CartController;
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

$router->get('/adminDashboard', [DashboardController::class, 'adminDashboard']);
$router->get('/UpdateUserStatus', [DashboardController::class, 'UpdateUserStatus']);
$router->get('/deleteEvent/{id}', [DashboardController::class, 'deleteEvent']);
$router->get('/deleteCategory/{id}', [DashboardController::class, 'deleteCategory']);

$router->post('/addCategory', [DashboardController::class, 'AddCategory']);

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

//event
$router->get('/event/{id}/edit', [OrganizerController::class, 'editEvent']);
$router->post('/event/{id}/edit', [OrganizerController::class, 'editEvent']);
$router->get('/event/{id}/export/pdf', [OrganizerController::class, 'exportParticipantsPDF']);




$router->get('/events', [EventController::class, 'index']);
$router->get('/event/{id}', [EventController::class, 'show']);

$router->get('/cart', [CartController::class, 'index']);

$router->get('/reserve/{id}', [EventController::class, 'reserve']);

$router->dispatch();
