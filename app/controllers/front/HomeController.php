<?php
namespace App\controllers\front;

use App\Core\Controller;

class HomeController extends Controller{

    public function index(){
        $this->view('front/home');
    }
}
