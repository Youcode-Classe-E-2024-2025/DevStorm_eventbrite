<?php

namespace App\controllers\back;
use App\Core\Controller;
use App\Core\Session;
use App\Models\Event;
use App\Models\User;
use App\models\Article;

class DashboardController extends Controller{

    public function adminDashboard(){
        $event = new Event();
        $events = $event->getAllEvents();
        $this->view('front/adminDashboard',['events'=>$events]);
    }

}