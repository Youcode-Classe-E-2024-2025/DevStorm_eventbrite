<?php
namespace App\controllers\front;

use App\Core\Controller;
use App\models\Event;
use App\models\Category;

class EventController extends Controller 
{
    public function index()
    {
        $event = new Event();
        $category = new Category();
        
        $events = $event->getFeaturedEvents();
        $categories = $category->getAllCategories();
        
        $this->view('front/event/index', [
            'events' => $events,
            'categories' => $categories
        ]);
    }
}