<?php

namespace App\controllers\back;

use App\Core\Controller;

class ErrorController extends Controller
{

    public function _404(){
        $this->view('errors/404');
    }

    public function _403(){
        $this->view('errors/403');
    }
}