<?php
namespace App\controllers\front;

use App\Core\Controller;
use App\Core\Session;

class HomeController extends Controller{

    public function index(){
        $user = Session::getUser();
        if ($user && isset($user->avatar)) {
            $avatar = $user->avatar;
        } else {
            $avatar = '';
        }
        $this->view('front/home',["avatar"=>$avatar]);
    }
}