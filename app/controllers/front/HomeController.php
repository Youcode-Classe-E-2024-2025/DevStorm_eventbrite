<?php
namespace App\controllers\front;

use App\Core\Controller;

class HomeController extends Controller{

    public function index(){
        echo "fffffffffffff";
        $this->view('front/home');
    }
}
