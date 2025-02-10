<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class Event extends Model
{
    protected $table = 'events';

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
}
