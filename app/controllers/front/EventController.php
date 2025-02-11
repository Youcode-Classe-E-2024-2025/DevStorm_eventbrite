<?php
namespace App\controllers\front;

use App\Core\Auth;
use App\Core\Controller;
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
        echo "reserve $id";
        

    }
}