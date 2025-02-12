<?php
namespace App\controllers\front;

use App\Core\Controller;
<<<<<<< HEAD
=======
use App\Core\Session;
>>>>>>> dfb0c8942ccc4055d0d0827fbc787eb69a1859c5

class HomeController extends Controller{

    public function index(){
<<<<<<< HEAD
        $this->view('front/home');
    }
}
=======
        $user = Session::getUser();
        if ($user && isset($user->avatar)) {
            $avatar = $user->avatar;
        } else {
            $avatar = '';
        }
        $this->view('front/home',["avatar"=>$avatar]);
    }
}
>>>>>>> dfb0c8942ccc4055d0d0827fbc787eb69a1859c5
