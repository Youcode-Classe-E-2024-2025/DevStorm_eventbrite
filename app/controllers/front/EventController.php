<?php
namespace App\controllers\front;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\enums\Role;
use App\models\Event;
use App\models\Category;

class EventController extends Controller 
{
    public function index()
    {

        $user = Session::getUser();
        if ($user && isset($user->avatar)) {
            $avatar = $user->avatar;
        } else {
            $avatar = '';
        }
        $event = new Event();
        $category = new Category();
        $uri=[];
        $uri['search'] = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
        $uri['category'] = isset($_GET['category']) ? htmlspecialchars($_GET['category']) : '';

        $events = $event->getFeaturedEvents($uri['search'],$uri['category']);
        $categories = $category->getAllCategories();
        
        $this->view('front/event/index', [
            'uri'=>$uri,
            'events' => $events,
            'categories' => $categories,
            'green' =>Session::getFlashMessage('green'),
            'red' => Session::getFlashMessage('red'),
            "avatar"=>$avatar,
            "user"=>$user
        ]);
    }
    public function show($id){
        $event= Event::read($id);
        $user = Session::getUser();
        if ($user && isset($user->avatar)) {
            $avatar = $user->avatar;
        } else {
            $avatar = '';
        }
        $this->view('front/event/details', [
           'event'=>$event,
           'green' =>Session::getFlashMessage('green'),
            'red' => Session::getFlashMessage('red'),
            'user'=>$user,
            "avatar"=>$avatar
        ]);
    }
    public function reserve($id){
        Auth::requireAuth(Role::PARTICIPANT);
        // echo "reserve $id";

        $type= $_GET['ticket'] ?? 'hh';
        $ticketsTypes = array("gratuit", "vip", "payant", "early bird");
        if (!in_array(strtolower($type) ,$ticketsTypes) || empty($id) || !is_numeric($id)) { 
            $this->redirect('/404');
        }

        $event = Event::read($id);
        if (!$event) {
            Session::setFlashMessage('red','event not found.');
            $this->redirect('/events');
        }

        if (!$event->isOpenForReservations()) {
            Session::setFlashMessage('red','Sorry ! This event is no longer accepting reservations.');
            $this->redirect("/event/$id");
        }

        $userId = Session::getUser()['id']; 
        if($event->addReservation($userId,'VIP')){
            Session::setFlashMessage('green',"event #{$event->title} has been successfully reserved.");
            $this->redirect("/cart");
        
        }
        Session::setFlashMessage('red',"Failed to reserve event: ");
        $this->redirect("/event/$id");
        
    }

}