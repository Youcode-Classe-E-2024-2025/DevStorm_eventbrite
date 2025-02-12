<?php
namespace App\models;

use App\Core\Database;
use App\Core\Model;
use PDO;

class Event extends Model
{
    protected $table = 'events';
    public $id;
    public $title;
    public $description;
    public $date;
    public $price;
    public $capacity;
    public $organizer;
    public $location;
    public $category;
    public $status = 'en attente';
    public $image_url;
    public $video_url;

    public function __construct($id=null,$title=null,$description=null,$date=null,$price = null,$capacity = null,$organizer = null,$location = null,$category = null,$status = null)
    {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->date = $date;
        $this->price = $price;
        $this->capacity = $capacity;
        $this->organizer = $organizer;
        $this->location = $location;
        $this->category = $category;
        $this->status = $status;
    }

    public function getFeaturedEvents($search = '', $category_id = '')
    {
        $db = \App\Core\Database::getInstance();
        $connection = $db->getConnection();

        $query = "SELECT e.*, c.name AS category_name 
                  FROM events e 
                  JOIN categories c ON e.category_id = c.id 
                  WHERE e.status = 'actif'";

        $params = [];

        if (!empty($category_id)) {
            $query .= " AND e.category_id = :category_id";
            $params[':category_id'] = $category_id;
        }

        if (!empty($search)) {
            $query .= " AND e.title LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }

        $stmt = $connection->prepare($query);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return self::toObjects($rows);
    }

   
    public function save()
    {
        $db = \App\Core\Database::getInstance();
        $sql = "INSERT INTO events (title, description, date, price, capacity, organizer_id, location, category_id, status, image_url, video_url) 
            VALUES (:title, :description, :date, :price, :capacity, :organizer_id, :location, :category_id, :status, :image_url, :video_url)";
    
        $stmt = $db->getConnection()->prepare($sql);
        return $stmt->execute([
            'title' => $this->title,
            'description' => $this->description,
            'date' => $this->date,
            'price' => $this->price,
            'capacity' => $this->capacity,
            'organizer_id' => $this->$organizer,
            'location' => $this->location,
            'category_id' => $this->category_id,
            'status' => $this->status,
            'image_url' => $this->image_url ?? null,
            'video_url' => $this->video_url ?? null
        ]);
    }
    public function getEventsByOrganizer($organizerId)
{
    $db = \App\Core\Database::getInstance();
    $query = $db->getConnection()->prepare("
        SELECT e.*, c.name as category_name 
        FROM events e 
        JOIN categories c ON e.category_id = c.id 
        WHERE e.organizer_id = :organizer_id 
        ORDER BY e.date DESC
    ");
    
    $query->execute(['organizer_id' => $organizerId]);
    return $query->fetchAll(PDO::FETCH_ASSOC);
}
/**
 * Gets comprehensive statistics for a specific event
 * @param int $eventId The event ID
 * @return array Statistics including sales, revenue, and attendance
 */
public function getEventStats($eventId)
{
    $db = \App\Core\Database::getInstance();
    $query = $db->getConnection()->prepare("
        SELECT 
            COUNT(t.id) as total_tickets,
            SUM(t.price) as total_revenue,
            COUNT(CASE WHEN t.status = 'validé' THEN 1 END) as validated_tickets,
            e.capacity as capacity
        FROM events e
        LEFT JOIN tickets t ON e.id = t.event_id
        WHERE e.id = :event_id
        GROUP BY e.id, e.capacity
    ");
    
    $query->execute(['event_id' => $eventId]);
    return $query->fetch(PDO::FETCH_ASSOC);
}

/**
 * Get event by ID with category information
 */
public function getEventById($eventId)
{
    $db = \App\Core\Database::getInstance();
    $query = $db->getConnection()->prepare("
        SELECT e.*, c.name as category_name 
        FROM events e 
        JOIN categories c ON e.category_id = c.id 
        WHERE e.id = :id
    ");
    
    $query->execute(['id' => $eventId]);
    return $query->fetch(PDO::FETCH_ASSOC);
}

/**
 * Update event details
 */
public function updateEvent($eventId, $data)
{
    $db = \App\Core\Database::getInstance();
    $sql = "UPDATE events SET 
            title = :title,
            description = :description,
            date = :date,
            price = :price,
            capacity = :capacity,
            location = :location,
            category_id = :category_id
            WHERE id = :id";
            
    $stmt = $db->getConnection()->prepare($sql);
    return $stmt->execute([
        'id' => $eventId,
        'title' => $data['title'],
        'description' => $data['description'],
        'date' => $data['date'],
        'price' => $data['price'],
        'capacity' => $data['capacity'],
        'location' => $data['location'],
        'category_id' => $data['category_id']
    ]);
}



 /**
 * Get all participants for an event
 */
public function getEventParticipants($eventId)
{
    $db = \App\Core\Database::getInstance();
    $query = $db->getConnection()->prepare("
        SELECT u.name, u.email, t.ticket_type, t.status
        FROM tickets t
        JOIN users u ON t.user_id = u.id
        WHERE t.event_id = :event_id
        
    ");
    
    $query->execute(['event_id' => $eventId]);
    return $query->fetchAll(PDO::FETCH_ASSOC);
}

        public function getAllEvents(){
                $db = \App\Core\Database::getInstance();
                $query = $db->getConnection()->prepare("SELECT events.id,  events.title AS event_name, users.name AS organizer_name, events.status, events.created_at 
                    FROM events JOIN users ON users.id = events.organizer_id");
                $query->execute();
                return $query->fetchAll(PDO::FETCH_ASSOC);
        }

        public function delete($id){
        $db = \App\Core\Database::getInstance();
        $query = $db->getConnection()->prepare("DELETE FROM events WHERE id = :id");
        $query->execute(['id' => $id]);
        }



    /**
     * create article objects 
     * 
     * @param array $rows
     * @return array
     */
    private static function toObjects($rows){
        $events=[];
        foreach ($rows as $row) {
             $organizer = User::read($row['organizer_id']) ?? new User();
             $category = Category::read($row['category_id']) ?? new Category();
             $events[] = new Event($row['id'], $row['title'], $row['description'],$row['date'],$row['price'],$row['capacity'],$organizer,$row['location'],$category,$row['status']);
         }
         return $events;
       }

       public static function read($id)
       {
           $db = Database::getInstance();
           $sql = "SELECT * FROM events WHERE id = :id";
           $query = $db->getConnection()->prepare($sql);
           $query->bindParam(':id', $id);
           $query->execute();
           $row = $query->fetch();

           $user=null;
           if($row){
               $organizer = User::read($row['organizer_id']) ?? new User();
               $category = Category::read($row['category_id']) ?? new Category();
               $event = new Event($row['id'], $row['title'], $row['description'],$row['date'],$row['price'],$row['capacity'],$organizer,$row['location'],$category,$row['status']);
           }
           return $event;
       } 
}
