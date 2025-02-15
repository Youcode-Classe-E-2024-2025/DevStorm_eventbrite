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

    public function __construct($id=null,$title=null,$description=null,$date=null,$price = null,$capacity = null,$organizer = null,$location = null,$category = null,$status = null,$image_url = null,
    $video_url = null)
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
        $this->image_url = $image_url;
        $this->video_url = $video_url;
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
        $db = Database::getInstance();
        $connection = $db->getConnection();
        
        try {
            $connection->beginTransaction();
            
            $sql = "INSERT INTO events (
                title, 
                description, 
                date, 
                location, 
                category_id, 
                organizer_id, 
                status, 
                image_url, 
                video_url
            ) VALUES (
                :title, 
                :description, 
                :date, 
                :location, 
                :category_id, 
                :organizer_id, 
                :status, 
                :image_url, 
                :video_url
            ) RETURNING id";
            
            $stmt = $connection->prepare($sql);
            $result = $stmt->execute([
                'title' => $this->title,
                'description' => $this->description,
                'date' => $this->date,
                'location' => $this->location,
                'category_id' => $this->category->id,
                'organizer_id' => $this->organizer->id,
                'status' => $this->status,
                'image_url' => $this->image_url ?? null,
                'video_url' => $this->video_url ?? null
            ]);
            
            if ($result) {
                $this->id = $stmt->fetchColumn();
                $connection->commit();
                return $this->id;
            }
            
            $connection->rollBack();
            return false;
            
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
    }
    
public function addEventTags($tags)
{
    if (!$this->id || empty($tags)) {
        return false;
    }
    
    $db = Database::getInstance();
    $connection = $db->getConnection();
    
    try {
        $connection->beginTransaction();
        
        $sql = "INSERT INTO event_tags (event_id, tag_id) VALUES (:event_id, :tag_id)";
        $stmt = $connection->prepare($sql);
        
        foreach ($tags as $tagId) {
            $stmt->execute([
                'event_id' => $this->id,
                'tag_id' => $tagId
            ]);
        }
        
        $connection->commit();
        return true;
        
    } catch (\Exception $e) {
        $connection->rollBack();
        return false;
    }
}

public function getEventPromotions($eventId)
{
    $db = \App\Core\Database::getInstance();
    $query = $db->getConnection()->prepare("
        SELECT p.* 
        FROM promotions p
        JOIN event_promotions ep ON p.id = ep.promotion_id
        WHERE ep.event_id = :event_id
        ORDER BY p.created_at DESC
    ");

    $query->execute(['event_id' => $eventId]);
    return $query->fetchAll(PDO::FETCH_ASSOC);
}
public function getSalesData($eventId)
{
    $db = \App\Core\Database::getInstance();
    $query = $db->getConnection()->prepare("
        SELECT 
            DATE(purchase_date) as date,
            COUNT(*) as count
        FROM tickets
        WHERE event_id = :event_id
        GROUP BY DATE(purchase_date)
        ORDER BY date DESC
        LIMIT 7
    ");

    $query->execute(['event_id' => $eventId]);
    return $query->fetchAll(PDO::FETCH_ASSOC);
}
public function getTicketTypeDistribution($eventId)
{
    $db = \App\Core\Database::getInstance();
    $query = $db->getConnection()->prepare("
        SELECT 
            ticket_type,
            COUNT(*) as count
        FROM tickets
        WHERE event_id = :event_id
        GROUP BY ticket_type
    ");

    $query->execute(['event_id' => $eventId]);
    $distribution = $query->fetchAll(PDO::FETCH_ASSOC);

    return array_column($distribution, 'count');
}
public function getEventsByOrganizer($organizerId)
{
    $db = \App\Core\Database::getInstance();
    $query = $db->getConnection()->prepare("
        SELECT 
            e.*,
            c.name as category_name,
            COALESCE(SUM(ett.total_quantity), 0) as total_capacity,
            COALESCE(SUM(ett.available_quantity), 0) as available_capacity
        FROM events e
        LEFT JOIN categories c ON e.category_id = c.id
        LEFT JOIN event_ticket_types ett ON e.id = ett.event_id
        WHERE e.organizer_id = :organizer_id
        GROUP BY e.id, e.title, e.date, e.status, e.location, c.name
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
            e.id,
            COALESCE(SUM(ett.total_quantity), 0) AS total_capacity,
            COALESCE(SUM(ett.available_quantity), 0) AS available_capacity,
            COUNT(t.id) AS total_tickets,
            COALESCE(SUM(t.price), 0) AS total_revenue,
            COUNT(CASE WHEN t.status = 'validé' THEN 1 END) AS validated_tickets
        FROM events e
        LEFT JOIN event_ticket_types ett ON e.id = ett.event_id
        LEFT JOIN tickets t ON t.ticket_type_id = ett.id
        WHERE e.id = :event_id
        GROUP BY e.id
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
public function updateEventTags($eventId, $newTags)
{
    $db = Database::getInstance();
    $connection = $db->getConnection();
    
    try {
        $connection->beginTransaction();
        
        // Remove existing tags
        $sql = "DELETE FROM event_tags WHERE event_id = :event_id";
        $stmt = $connection->prepare($sql);
        $stmt->execute(['event_id' => $eventId]);
        
        // Add new tags
        $sql = "INSERT INTO event_tags (event_id, tag_id) VALUES (:event_id, :tag_id)";
        $stmt = $connection->prepare($sql);
        
        foreach ($newTags as $tagId) {
            $stmt->execute([
                'event_id' => $eventId,
                'tag_id' => $tagId
            ]);
        }
        
        $connection->commit();
        return true;
    } catch (\Exception $e) {
        $connection->rollBack();
        return false;
    }
}
public function getEventTags($eventId)
{
    $db = Database::getInstance();
    $sql = "SELECT tag_id FROM event_tags WHERE event_id = :event_id";
    $stmt = $db->getConnection()->prepare($sql);
    $stmt->execute(['event_id' => $eventId]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}
public function updateEvent($eventId, $data)
{
    $db = \App\Core\Database::getInstance();
    
    $updateFields = [];
    $params = ['id' => $eventId];
    
    foreach ($data as $key => $value) {
        $updateFields[] = "$key = :$key";
        $params[$key] = $value;
    }
    
    $sql = "UPDATE events SET " . implode(', ', $updateFields) . " WHERE id = :id";
    
    $stmt = $db->getConnection()->prepare($sql);
    return $stmt->execute($params);
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
    private static function toObjects($rows)
    {
        $events = [];
        foreach ($rows as $row) {
            $organizer = User::read($row['organizer_id']) ?? new User();
            $category = Category::read($row['category_id']) ?? new Category();


            $db = Database::getInstance();
            $ticketSql = "SELECT price, total_quantity FROM event_ticket_types WHERE event_id = :event_id";
            $ticketQuery = $db->getConnection()->prepare($ticketSql);
            $ticketQuery->bindParam(':event_id', $row['id']);
            $ticketQuery->execute();
            $ticketRow = $ticketQuery->fetch();

            $price = $ticketRow ? $ticketRow['price'] : null;
            $capacity = $ticketRow ? $ticketRow['total_quantity'] : null;

            $events[] = new Event(
                $row['id'],
                $row['title'],
                $row['description'],
                $row['date'],
                $price, // Updated price
                $capacity, // Updated capacity
                $organizer,
                $row['location'],
                $category,
                $row['status'],
                $row['image_url'],
                $row['video_url']
            );
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

        $event = null;
        if ($row) {
            $organizer = User::read($row['organizer_id']) ?? new User();
            $category = Category::read($row['category_id']) ?? new Category();

            $ticketSql = "SELECT price, total_quantity FROM event_ticket_types WHERE event_id = :event_id";
            $ticketQuery = $db->getConnection()->prepare($ticketSql);
            $ticketQuery->bindParam(':event_id', $row['id']);
            $ticketQuery->execute();
            $ticketRow = $ticketQuery->fetch();

            $price = $ticketRow ? $ticketRow['price'] : null;
            $capacity = $ticketRow ? $ticketRow['total_quantity'] : null;

            // Fetch the image_url and video_url
            $imageUrl = $row['image_url'] ?? null;
            $videoUrl = $row['video_url'] ?? null;

            // Create the event object with the added URLs
            $event = new Event(
                $row['id'],
                $row['title'],
                $row['description'],
                $row['date'],
                $price, // Updated price
                $capacity, // Updated capacity
                $organizer,
                $row['location'],
                $category,
                $row['status'],
                $imageUrl,
                $videoUrl
            );
        }
        return $event;
    }

        public function addReservation($userId): bool
        {
        if (!$this->isOpenForReservations()) {
            return false;
        }

        if (!$this->hasAvailableSpots()) {
            return false; 
        }

        $ticket = new Ticket(null, $this, User::read($userId), 'payant', $this->price, null, 'réservé');
        if (!$ticket->save()) {
            return false; 
        }
        return true; 
    }

    /**
     * Checks if the event is open for reservations.
     *
     * @return bool True if the event is open for reservations, false otherwise.
     */
    public function isOpenForReservations(): bool
    {
        return $this->status === 'actif';
    }

    /**
     * Checks if the event has available spots.
     *
     * @return bool True if there are available spots, false otherwise.
     */
    private function hasAvailableSpots(): bool
    {
        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) AS reserved_count FROM tickets WHERE event_id = :event_id";
        $stmt = $db->getConnection()->prepare($sql);
        $stmt->execute([':event_id' => $this->id]);
        $reservedCount = $stmt->fetch(PDO::FETCH_ASSOC)['reserved_count'];

        return $reservedCount < $this->capacity;
    }


    public function getAllEventStats()
    {
        $db = \App\Core\Database::getInstance();
        $query = $db->getConnection()->prepare("
        SELECT 
            COUNT(t.id) as total_tickets,
            SUM(t.price) as total_revenue,
            COUNT(CASE WHEN t.status = 'validé' THEN 1 END) as validated_tickets,
            e.capacity as capacity
        FROM events e
         JOIN tickets t ON e.id = t.event_id
        GROUP BY e.id, e.capacity
    ");

        $query->execute();
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function UpdateStatus($status,$id){
        $db = \App\Core\Database::getInstance();
        $query = $db->getConnection()->prepare("UPDATE events SET status = :status WHERE id = :id");
        $query->execute(['status' => $status, 'id' => $id]);
    }


    public function findAll($limit, $offset, $organizer_id) 
    {
        $db = \App\Core\Database::getInstance();
        $query = $db->getConnection()->prepare("
            SELECT 
                e.*,
                COALESCE(SUM(ett.total_quantity), 0) as total_capacity,
                COALESCE(SUM(ett.available_quantity), 0) as available_capacity
            FROM events e
            LEFT JOIN event_ticket_types ett ON e.id = ett.event_id
            WHERE e.organizer_id = :organizer_id
            GROUP BY e.id
            ORDER BY e.date DESC
            LIMIT :limit OFFSET :offset
        ");
        
        $query->bindValue(':organizer_id', $organizer_id, PDO::PARAM_INT);
        $query->bindValue(':limit', $limit, PDO::PARAM_INT);
        $query->bindValue(':offset', $offset, PDO::PARAM_INT);
        $query->execute();
        
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function count($organizer_id = null) {
        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) as count FROM events";
        
        if ($organizer_id) {
            $sql .= " WHERE organizer_id = :organizer_id";
        }
        
        $stmt = $db->getConnection()->prepare($sql);
        
        if ($organizer_id) {
            $stmt->bindValue(':organizer_id', $organizer_id, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }















    public function addTicketTypes($eventId, $ticketTypes) 
    {
        $db = Database::getInstance();
        $connection = $db->getConnection();
        
        try {
            $connection->beginTransaction();
            
            $sql = "INSERT INTO event_ticket_types (
                event_id,
                ticket_type,
                price,
                total_quantity,
                available_quantity
            ) VALUES (
                :event_id,
                :ticket_type,
                :price,
                :total_quantity,
                :available_quantity
            )";
            
            $stmt = $connection->prepare($sql);
            
            foreach ($ticketTypes as $ticket) {
                $stmt->execute([
                    'event_id' => $eventId,
                    'ticket_type' => $ticket['type'],
                    'price' => $ticket['price'],
                    'total_quantity' => $ticket['quantity'],
                    'available_quantity' => $ticket['quantity']
                ]);
            }
            
            $connection->commit();
            return true;
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
    }

//deleteEvent 

public function canDeleteEvent($id)
{
    $db = \App\Core\Database::getInstance();
    
    // Check for participants
    $participantsQuery = $db->getConnection()->prepare("
        SELECT COUNT(*) as count 
        FROM tickets 
        WHERE event_id = :id 
        AND status != 'annulé'
    ");
    $participantsQuery->execute(['id' => $id]);
    $hasParticipants = $participantsQuery->fetch(PDO::FETCH_ASSOC)['count'] > 0;

    // Check if event is past
    $eventQuery = $db->getConnection()->prepare("
        SELECT date < CURRENT_TIMESTAMP as is_past
        FROM events 
        WHERE id = :id
    ");
    $eventQuery->execute(['id' => $id]);
    $isPast = $eventQuery->fetch(PDO::FETCH_ASSOC)['is_past'];

    // Event can be deleted if it has no participants AND is past
    return !$hasParticipants && $isPast;
}


public function deleteEvent($id)
{
    error_log("Attempting to delete event $id");
    $db = \App\Core\Database::getInstance();
    
    $query = $db->getConnection()->prepare("DELETE FROM events WHERE id = :id");
    $success = $query->execute(['id' => $id]);
    
    error_log("Delete success: " . ($success ? 'true' : 'false'));
    return $success;
}


}
