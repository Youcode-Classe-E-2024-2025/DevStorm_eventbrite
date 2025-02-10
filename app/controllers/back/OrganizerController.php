<?php
namespace App\Controllers\back;

use App\Core\Controller;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\Promotion;

class OrganizerController extends Controller 
{
    public function dashboard()
    {
        $event = new Event();
        $events = $event->getEventsByOrganizer($_SESSION['user_id']);
        $this->view('organizer/dashboard', ['events' => $events]);
    }

    public function createEvent()
    {
        $this->view('organizer/create-event');
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
        
        if ($event->save()) {
            $this->redirect('/organizer/dashboard');
        }
    }

    public function eventStats($eventId)
    {
        $ticket = new Ticket();
        $stats = $ticket->getEventStats($eventId);
        $this->view('organizer/stats', ['stats' => $stats]);
    }

    public function exportParticipants($eventId)
    {
        $ticket = new Ticket();
        $participants = $ticket->getEventParticipants($eventId);
    }
}
