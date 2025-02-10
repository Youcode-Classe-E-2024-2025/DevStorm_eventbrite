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
        $event->location = $_POST['location'];
        $event->category_id = $_POST['category_id'];
        
        if ($event->save()) {
            $this->redirect('/organizer/dashboard');
        }
    }

    public function salesStats($eventId)
    {
        $ticket = new Ticket();
        $stats = $ticket->getEventStats($eventId);
        $this->view('back/organizer/stats', ['stats' => $stats]);
    }
 /**
 * Display event statistics
 */
public function eventStats($eventId)
{
    $event = new Event();
    $stats = $event->getEventStats($eventId);
    
    $this->view('front/event/stats', [
        'stats' => $stats,
        'event_id' => $eventId
    ]);
}


    public function exportParticipants($eventId)
    {
        $ticket = new Ticket();
        $participants = $ticket->getEventParticipants($eventId);
        //more 
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="participants.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Name', 'Email', 'Ticket Type', 'Purchase Date']);
        
        foreach ($participants as $participant) {
            fputcsv($output, [
                $participant['name'],
                $participant['email'],
                $participant['ticket_type'],
                $participant['purchase_date']
            ]);
        }
    }
}
