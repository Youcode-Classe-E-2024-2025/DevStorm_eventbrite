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
            'categories' => $categories
        ]);
    }
    public function show($id){
        $event= Event::read($id);
        // var_dump($event);
        $this->view('front/event/details', [
           'event'=>$event
        ]);
    }
    public function reserve($id){
        Auth::requireAuth(Role::PARTICIPANT);
        // echo "reserve $id";

        if (empty($id) || !is_numeric($id)) {
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
        
        if($event->addReservation($userId)){
            Session::setFlashMessage('green',"event #{$event->title} has been successfully reserved.");
            $this->redirect("/cart");
        
        }
        Session::setFlashMessage('red',"Failed to reserve event: ");
        $this->redirect("/event/$id");
        
    }

}