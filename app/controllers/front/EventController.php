<?php
namespace App\Controllers\front;

use App\Core\Controller;
use App\Models\Event;
use App\Models\Category;

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