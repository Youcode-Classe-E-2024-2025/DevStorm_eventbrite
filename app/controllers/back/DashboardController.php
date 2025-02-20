<?php

namespace App\controllers\back;
use App\Core\Controller;
use App\Core\Security;
use App\Core\Session;
use App\models\Event;
use App\models\User;
use App\models\Category;
use App\models\Tag;

class DashboardController extends Controller{

    public function adminDashboard(){
        $event = new Event();
        $User = new User();
        $category = new Category();
        $tag = new Tag();
        $tags = $tag->getAllTags();
        $categories = $category->getAllCategories();
        $users = $User->fetchAll();
        $events = $event->getAllEvents();
        $stats = $event->getAllEventStats();
        $user = Session::getUser();
        if ($user && isset($user->avatar)) {
            $avatar = $user->avatar;
        } else {
            $avatar = '';
        }
        $this->view('front/adminDashboard',['events'=>$events,'user'=>$user,"avatar"=>$avatar,'users'=>$users,'categories'=>$categories,'tags'=>$tags, 'stats'=>$stats]);
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

    public function updateEventStatus():void{
        $event = new Event();
        $user = new User();
        $status  = $_POST['status'];
        $id = $_POST['event_id'];
        $event->UpdateStatus($status,$id);
        $userr = $user->getUserByEvent($id);
        $organizer_email = $userr->email;
        $user->sendEmail($organizer_email,$status);
        $this->adminDashboard();
    }


    public function deleteEvent($id){
        $event = new Event();
        $event->delete($id);
        $this->adminDashboard();
    }

    public function deleteCategory($id){
        $Category = new Category();
        $Category->delete($id);
        $this->adminDashboard();
    }

    public function AddCategory(){
        $categoryname = Security::XSS($_POST['categoryName']);
        $Category = new Category(null, $categoryname);
        $Category->create();
        $this->adminDashboard();
    }
    public function deleteTag($id){
        $Tag = new Tag();
        $Tag->delete($id);
        $this->adminDashboard();
    }

    public function AddTag(){
        $tagName = Security::XSS($_POST['tagName']);
        $Tag = new Tag(null,$tagName);
        $Tag->create();
        $this->adminDashboard();
    }


}