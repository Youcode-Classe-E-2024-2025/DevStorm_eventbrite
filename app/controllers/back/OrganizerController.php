<?php
namespace App\Controllers\back;

use App\Core\Controller;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\Promotion;
use App\Models\Category; 

class OrganizerController extends Controller 
{
    public function dashboard()
    {
        session_start();
        //pour tester
        $_SESSION['user_id'] = 1;
        $event = new Event();
        $events = $event->getEventsByOrganizer($_SESSION['user_id']);
        $stats = [
            'total_events' => count($events),
            'total_tickets' => 0,
            'total_revenue' => 0
        ];
        
        $this->view('back/organizer/dashboard', [
            'events' => $events,
            'stats' => $stats
        ]);
    }

    public function createEvent()
    {
        $category = new Category();
        $categories = $category->getAllCategories();
        
        $this->view('front/event/create-event', [
            'categories' => $categories
        ]);
    }

    public function handleCreateEvent()
    {
        $event = new Event();
        $event->title = $_POST['title'];
        $event->description = $_POST['description'];
        $event->date = $_POST['date'];
        $event->price = $_POST['price'];
        $event->capacity = $_POST['capacity'];
        $event->organizer_id = $_SESSION['user_id'];
        //more 
       
    }

  
   

   
}
