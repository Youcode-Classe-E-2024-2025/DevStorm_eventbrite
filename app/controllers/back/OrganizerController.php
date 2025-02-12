<?php

namespace App\controllers\back;
use App\Core\Controller;
use App\Core\Session;
use App\models\Event;
use App\models\Promotion;
use App\models\Ticket;
use App\models\User;
use App\models\Category; 
use App\models\Tag; 
use App\traits\PDFGenerator ; 
use TCPDF; 

class OrganizerController extends Controller 
{

        public function dashboard()
    {
        // session_start();
        //pour tester
        
        $event = new Event();
        $events = $event->getEventsByOrganizer(Session::getUser()['id']);
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

        $tag = new Tag();
        $tags = $tag->getAllTags();
    
        
        $this->view('front/event/create-event', [
            'categories' => $categories,
            'tags' => $tags
        ]);
    }

    public function handleCreateEvent()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $event = new Event();
            
            $imageUploadDir = 'assets/images/';
            $videoUploadDir = 'assets/videos/';
    
            // Handle image upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $imageInfo = pathinfo($_FILES['image']['name']);
                $imageExtension = strtolower($imageInfo['extension']);
                $allowedImageExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                
                if (in_array($imageExtension, $allowedImageExtensions)) {
                    $newImageName = uniqid() . '.' . $imageExtension;
                    $imagePath = $imageUploadDir . $newImageName;
                    
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
                        $event->image_url = $imagePath;
                    }
                }
            }
    
            // Handle video upload
            if (isset($_FILES['video']) && $_FILES['video']['error'] == 0) {
                $videoInfo = pathinfo($_FILES['video']['name']);
                $videoExtension = strtolower($videoInfo['extension']);
                $allowedVideoExtensions = ['mp4', 'mov', 'avi'];
                
                if (in_array($videoExtension, $allowedVideoExtensions)) {
                    $newVideoName = uniqid() . '.' . $videoExtension;
                    $videoPath = $videoUploadDir . $newVideoName;
                    
                    if (move_uploaded_file($_FILES['video']['tmp_name'], $videoPath)) {
                        $event->video_url = $videoPath;
                    }
                }
            }
    
            // Set other event properties
            $event->title = $_POST['title'];
            $event->description = $_POST['description'];
            $event->date = $_POST['date'];
            $event->price = $_POST['price'];
            $event->capacity = $_POST['capacity'];
            $event->location = $_POST['location'];
            $event->category = Category::read($_POST['category_id']);
            $event->status = 'en attente';
            $event->organizer = User::read(Session::getUser()['id']);
    
            try {
                $eventId = $event->save();
                if ($eventId) {
                    if (isset($_POST['tags']) && is_array($_POST['tags'])) {
                        $event->addEventTags($_POST['tags']);
                    }
                    
                    header('Location: /organizer/dashboard');
                    exit;
                }
            } catch (\Exception $e) {
                $_SESSION['error'] = "Error creating event: " . $e->getMessage();
                header('Location: /organizer/event/create');
                exit;
            }
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
    
    
   
    public function editEvent($eventId)
    {
        $event = new Event();
        $category = new Category();
        $tag = new Tag();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Base event data
            $updateData = [
                'title' => $_POST['title'],
                'description' => $_POST['description'],
                'date' => $_POST['date'],
                'price' => $_POST['price'],
                'capacity' => $_POST['capacity'],
                'location' => $_POST['location'],
                'category_id' => $_POST['category_id']
            ];
    
            // Handle image upload
            if (!empty($_FILES['image']['name'])) {
                $newImageName = uniqid() . '_' . $_FILES['image']['name'];
                move_uploaded_file($_FILES['image']['tmp_name'], 'assets/images/' . $newImageName);
                $updateData['image_url'] = 'assets/images/' . $newImageName;
            }
            
            // Handle video upload
            if (!empty($_FILES['video']['name'])) {
                $newVideoName = uniqid() . '_' . $_FILES['video']['name'];
                move_uploaded_file($_FILES['video']['tmp_name'], 'assets/videos/' . $newVideoName);
                $updateData['video_url'] = 'assets/videos/' . $newVideoName;
            }
    
            // Update event data
            $event->updateEvent($eventId, $updateData);
            
            // Update tags if present
            if (isset($_POST['tags'])) {
                $event->updateEventTags($eventId, $_POST['tags']);
            }
            
            $this->redirect('/organizer/dashboard');
        }
        
        // Get all necessary data for the form
        $eventData = $event->getEventById($eventId);
        $categories = $category->getAllCategories();
        $tags = $tag->getAllTags();
        $selectedTags = $event->getEventTags($eventId);
        
        $this->view('front/event/edit-event', [
            'event' => $eventData,
            'categories' => $categories,
            'tags' => $tags,
            'selectedTags' => $selectedTags
        ]);
    }
    

}
