<?php

namespace App\controllers\back;
use App\Core\Controller;
use App\Core\Session;
use App\Models\Event;
use App\Models\User;
use App\Models\Category;

class DashboardController extends Controller{

    public function adminDashboard(){
        $event = new Event();
        $user = new User();
        $category = new Category();
        $categories = $category->getAllCategories();
        $users = $user->fetchAll();
        $events = $event->getAllEvents();
        $this->view('front/adminDashboard',['events'=>$events,'users'=>$users,'categories'=>$categories]);
    }

    public function UpdateUserStatus(){
        $user = new User();
        if (isset($_GET['action']) && isset($_GET['id'])) {
            $action = $_GET['action'];
            $userId = $_GET['id'];
            if ($action === 'actif') {
                $user->updateStatus($userId, 'actif');
            } elseif ($action === 'suspend') {
                $user->updateStatus($userId, 'suspended');
            } elseif ($action === 'delete') {
                $user->updateStatus($userId, 'deleted');
            }
            $this->adminDashboard();
        }
    }

    public function deleteEvent($id){
        $event = new Event();
        $event->delete($id);
        $this->adminDashboard();
    }



}