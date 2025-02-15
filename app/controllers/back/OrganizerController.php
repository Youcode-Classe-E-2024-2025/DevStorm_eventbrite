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
        $total_capacity = 0;
        $available_capacity = 0;

        $user = Session::getUser();
        if ($user && isset($user->avatar)) {
            $avatar = $user->avatar;
        } else {
            $avatar = '';
        }


       foreach ($events as $evt) {
        $eventStats = $event->getEventStats($evt['id']);
        $total_tickets += $eventStats['total_tickets'] ?? 0;
        $total_revenue += $eventStats['total_revenue'] ?? 0;
        $validated_tickets+=$eventStats['validated_tickets'] ?? 0;
        $total_capacity += $eventStats['total_capacity'] ?? 0;
        $available_capacity += $eventStats['available_capacity'] ?? 0;    }
    
        $stats = [
            'total_events' => count($events),
            'total_tickets' => $total_tickets,
            'total_revenue' => $total_revenue,
            'validated_tickets' =>$validated_tickets,
            'total_capacity' => $total_capacity,
            'available_capacity' => $available_capacity        ];
        
        $this->view('back/organizer/dashboard', [
            'events' => $events,
            'stats' => $stats,
            'user'=>$user,
            "avatar"=>$avatar
        ]);
    }

    public function createEvent()
    {
        $category = new Category();
        $categories = $category->getAllCategories();

        $tag = new Tag();
        $tags = $tag->getAllTags();

        $user = Session::getUser();
        if ($user && isset($user->avatar)) {
            $avatar = $user->avatar;
        } else {
            $avatar = '';
        }
    
        
        $this->view('front/event/create-event', [
            'categories' => $categories,
            'tags' => $tags,
            'user'=>$user,
            'avatar'=>$avatar,
        ]);
    }
    public function handleCreateEvent()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $event = new Event();
                
                // Handle file uploads
                $this->handleFileUploads($event);
                
                // Set event properties
                $event->title = $_POST['title'];
                $event->description = $_POST['description'];
                $event->date = $_POST['date'];
                $event->location = $_POST['location'];
                $event->category = Category::read($_POST['category_id']);
                $event->status = 'en attente';
                $event->organizer = User::read(Session::getUser()['id']);
    
                // Save the event
                $eventId = $event->save();
    
                if ($eventId) {
                    // Handle tags
                    if (isset($_POST['tags']) && is_array($_POST['tags'])) {
                        $event->addEventTags($_POST['tags']);
                    }
    
                    // Handle ticket types
                    if (isset($_POST['ticket_types']) && is_array($_POST['ticket_types'])) {
                        $ticketTypes = [];
                        foreach ($_POST['ticket_types'] as $type) {
                            $ticketTypes[] = [
                                'type' => $type['type'],
                                'price' => floatval($type['price']),
                                'quantity' => intval($type['quantity'])
                            ];
                        }
                        $event->addTicketTypes($eventId, $ticketTypes);
                    }
    
                    $_SESSION['success'] = "Event created successfully!";
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
    
    
    private function handleFileUploads($event)
    {
        $imageUploadDir = 'assets/images/';
        $videoUploadDir = 'assets/videos/';
    
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $imageInfo = pathinfo($_FILES['image']['name']);
            $imageExtension = strtolower($imageInfo['extension']);
            if (in_array($imageExtension, ['jpg', 'jpeg', 'png', 'gif'])) {
                $newImageName = uniqid() . '.' . $imageExtension;
                $imagePath = $imageUploadDir . $newImageName;
                move_uploaded_file($_FILES['image']['tmp_name'], $imagePath);
                $event->image_url = $imagePath;
            }
        }
    
        // Handle video upload
        if (isset($_FILES['video']) && $_FILES['video']['error'] == 0) {
            $videoInfo = pathinfo($_FILES['video']['name']);
            $videoExtension = strtolower($videoInfo['extension']);
            if (in_array($videoExtension, ['mp4', 'mov', 'avi'])) {
                $newVideoName = uniqid() . '.' . $videoExtension;
                $videoPath = $videoUploadDir . $newVideoName;
                move_uploaded_file($_FILES['video']['tmp_name'], $videoPath);
                $event->video_url = $videoPath;
            }
        }
    }
    

    public function salesStats($eventId)
    {
        $ticket = new Ticket();
        $stats = $ticket->getEventStats($eventId);
        $user = Session::getUser();
        if ($user && isset($user->avatar)) {
            $avatar = $user->avatar;
        } else {
            $avatar = '';
        }

        $this->view('back/organizer/stats', ['stats' => $stats, 'user'=>$user,'avatar'=>$avatar]);
    }
 /**
 * Display event statistics je change this methode par des nuoveuax statistics
 */
public function eventStats($eventId)
{
    $event = new Event();
    $stats = $event->getEventStats($eventId);
    $promotions = $event->getEventPromotions($eventId);
    $salesData = $event->getSalesData($eventId);

    $user = Session::getUser();
    if ($user && isset($user->avatar)) {
        $avatar = $user->avatar;
    } else {
        $avatar = '';
    }
    
    $this->view('front/event/stats', [
        'stats' => array_merge($stats, [
            'promotions' => $promotions,
            'sales_dates' => array_column($salesData, 'date'),
            'sales_data' => array_column($salesData, 'count'),
            'ticket_types_data' => $event->getTicketTypeDistribution($eventId),
        ]),
        'event_id' => $eventId,
        'user'=>$user,
        'avatar'=>$avatar
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

                'event_id' => $eventId,
                'discount_percentage' => $_POST['discount_percentage'],
                'valid_from' => $_POST['valid_from'],
                'valid_until' => $_POST['valid_until']
            ]);
            
            if ($result) {
                $this->redirect('/event/' . $eventId . '/promocodes');
            }
        }

        $user = Session::getUser();
        if ($user && isset($user->avatar)) {
            $avatar = $user->avatar;
        } else {
            $avatar = '';
        }
        $this->view('front/event/create-promocode', ['event_id' => $eventId,'user'=>$user,'avatar'=>$avatar]);
    }
    
    public function listPromoCodes($eventId)
    {
        $promotion = new Promotion();
        $promoCodes = $promotion->getPromotionsByEvent($eventId);
        $this->view('front/event/list-promocodes', [
            'promoCodes' => $promoCodes,
            'event_id' => $eventId
        ]);
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
    //delete event 
    public function deleteEvent($id)
    {
        header('Content-Type: application/json');
        $event = new Event();
        
        if ($event->canDeleteEvent($id)) {
            $success = $event->deleteEvent($id);
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Event deleted successfully' : 'Failed to delete event'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => "L'événement ne peut pas être supprimé car il a des participants ou n'est pas terminé"
            ]);
        }
        exit;
    }
    //pagination
    public function getEvents($page = 1) {
        header('Content-Type: application/json');
        
        $event = new Event();
        $organizer_id = Session::getUser()['id'];
        
        $limit = 6;
        $offset = ($page - 1) * $limit;
        
        $events = $event->findAll($limit, $offset, $organizer_id);
        $totalEvents = $event->count($organizer_id);
        $totalPages = ceil($totalEvents / $limit);
        
        echo json_encode([
            'events' => $events,
            'currentPage' => (int)$page,
            'totalPages' => $totalPages
        ]);
        exit;
    }
    

}
