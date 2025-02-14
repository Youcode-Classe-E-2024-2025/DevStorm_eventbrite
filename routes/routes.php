<?php

use App\controllers\back\UserController;
use App\controllers\back\DashboardController;
use App\controllers\back\ErrorController;
use App\controllers\front\HomeController;
use App\controllers\front\EventController;
use App\controllers\back\OrganizerController;
use App\controllers\back\PaimentController;
use App\controllers\back\WebhookController;
use App\controllers\front\CartController;
use App\Core\Router;

$router = new Router();


$router->get('/', [HomeController::class, 'index']);
$router->get('/contact', [HomeController::class, 'contact']);

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

$router->get('/deleteTag/{id}', [DashboardController::class, 'deleteTag']);

$router->post('/addCategory', [DashboardController::class, 'AddCategory']);
$router->post('/addTag', [DashboardController::class, 'addTag']);

$router->get('/google-login', [UserController::class, 'handleGoogleAuth']);

//organizer ROUTEs
$router->get('/organizer/event/create', [OrganizerController::class, 'createEvent']);
$router->post('/organizer/event/create', [OrganizerController::class, 'handleCreateEvent']);
$router->get('/organizer/event/stats/{id}', [OrganizerController::class, 'salesStats']);
$router->get('/organizer/event/export/{id}', [OrganizerController::class, 'exportParticipants']);
$router->get('/organizer/dashboard', [OrganizerController::class, 'dashboard']);
$router->get('/event/{id}/stats', [OrganizerController::class, 'eventStats']);

$router->get('/event/{id}/delete', [OrganizerController::class, 'deleteEvent']);
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
$router->post('/reservation/cancel/{id}', [CartController::class, 'cancelReservation']);

$router->get('/reserve/{id}', [EventController::class, 'reserve']);

// error
$router->get('/404', [ErrorController::class, '_404']);
$router->get('/403', [ErrorController::class, '_403']);

// paiment
$router->post('/checkout/{id}', [PaimentController::class, 'checkout']);
$router->get('/paiment/success', [PaimentController::class, 'paimentSuccess']);
$router->get('/paiment/cancel', [PaimentController::class, 'paimentCancel']);

// Webhook route
$router->post('/webhook/stripe', [WebhookController::class, 'handleWebhook']);

$router->dispatch();
