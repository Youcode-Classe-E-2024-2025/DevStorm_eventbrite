<?php

use App\Core\Security;
use App\Core\Session;

require_once __DIR__ . '/../vendor/autoload.php';

Session::start();
Security::Csrftoken();
require __DIR__ . '/../routes/routes.php';
