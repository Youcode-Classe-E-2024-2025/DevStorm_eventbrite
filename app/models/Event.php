<?php
namespace App\models;

use App\Core\Model;
use PDO;

class Event extends Model
{
    protected $table = 'events';
    public $title;
    public $description;
    public $date;
    public $price;
    public $capacity;
    public $organizer_id;
    public $location;
    public $category_id;
    public $status = 'en attente';

    public function getFeaturedEvents()
    {
        $db = \App\Core\Database::getInstance();
        $query = $db->getConnection()->prepare("
            SELECT e.*, c.name as category_name 
            FROM events e 
            JOIN categories c ON e.category_id = c.id 
            WHERE e.status = 'actif' 
            LIMIT 6
        ");
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function save()
    {
        $db = \App\Core\Database::getInstance();
        $sql = "INSERT INTO events (title, description, date, price, capacity, organizer_id, location, category_id, status) 
                VALUES (:title, :description, :date, :price, :capacity, :organizer_id, :location, :category_id, :status)";
        
        $stmt = $db->getConnection()->prepare($sql);
        return $stmt->execute([
            'title' => $this->title,
            'description' => $this->description,
            'date' => $this->date,
            'price' => $this->price,
            'capacity' => $this->capacity,
            'organizer_id' => $this->organizer_id,
            'location' => $this->location,
            'category_id' => $this->category_id,
            'status' => $this->status
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
            e.capacity
        FROM events e
        LEFT JOIN tickets t ON e.id = t.event_id
        WHERE e.id = :event_id
        GROUP BY e.id, e.capacity
    ");
    
    $query->execute(['event_id' => $eventId]);
    return $query->fetch(PDO::FETCH_ASSOC);
}


}
