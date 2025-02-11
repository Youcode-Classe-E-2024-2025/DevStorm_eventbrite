<?php

namespace App\controllers\back;
use App\Core\Controller;
use App\models\Event;
use App\models\Promotion;
use App\models\Ticket;
use App\models\Category; 
use App\traits\PDFGenerator ; 
use TCPDF; 

class OrganizerController extends Controller 
{

        public function dashboard()
    {
        session_start();
        //pour tester
        $_SESSION['user_id'] = 2;
        $event = new Event();
        $events = $event->getEventsByOrganizer($_SESSION['user_id']);
        $total_tickets = 0;
        $total_revenue = 0;
        $validated_tickets=0;
        $capacity=0;
       foreach ($events as $evt) {
        $eventStats = $event->getEventStats($evt['id']);
        $total_tickets += $eventStats['total_tickets'] ?? 0;
        $total_revenue += $eventStats['total_revenue'] ?? 0;
        $validated_tickets+=$eventStats['validated_tickets'] ?? 0;
        $capacity += $eventStats['capacity'] ?? 0;
    }
    
        $stats = [
            'total_events' => count($events),
            'total_tickets' => $total_tickets,
            'total_revenue' => $total_revenue,
            'validated_tickets' =>$validated_tickets,
            'capacity' => $capacity
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

//composer require tecnickcom/tcpdf

use PDFGenerator;
public function exportParticipantsPDF($eventId)
    {    
        $event = new Event();
        $participants = $event->getEventParticipants($eventId);
        $eventDetails = $event->getEventById($eventId);

        $title = 'Participants - ' . $eventDetails['title'];
        $header = ['Nom', 'Email', 'Type Billet', 'Status'];
        $filename = 'participants_event_' . $eventId;

        // Use the trait method
        $this->generatePDF($title, $header, $participants, $filename);
    }

    public function createPromoCode($eventId)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $promotion = new Promotion();
            $result = $promotion->createPromotion([
                'discount_percentage' => $_POST['discount_percentage'],
                'valid_from' => $_POST['valid_from'],
                'valid_until' => $_POST['valid_until']
            ]);
            
            if ($result) {
                $this->redirect('/event/' . $eventId . '/promocodes');
            }
        }
        
        $this->view('front/event/create-promocode', ['event_id' => $eventId]);
    }
    public function listPromoCodes($eventId)
    {
        $promotion = new Promotion();
        $promoCodes = $promotion->getPromotionsByEvent();
        
        $this->view('front/event/list-promocodes', [
            'promoCodes' => $promoCodes,
            'event_id' => $eventId
        ]);
    }
    public function editEvent($eventId)
{
    $event = new Event();
    $category = new Category();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Handle form submission
        $event->updateEvent($eventId, [
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'date' => $_POST['date'],
            'price' => $_POST['price'],
            'capacity' => $_POST['capacity'],
            'location' => $_POST['location'],
            'category_id' => $_POST['category_id']
        ]);
        
        $this->redirect('/organizer/dashboard');
    }
    
    // Get event data for form
    $eventData = $event->getEventById($eventId);
    $categories = $category->getAllCategories();
    
    $this->view('front/event/edit-event', [
        'event' => $eventData,
        'categories' => $categories
    ]);
}

}
