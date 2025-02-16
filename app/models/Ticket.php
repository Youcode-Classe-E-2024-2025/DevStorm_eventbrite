<?php

namespace App\models;

use App\Core\Database;
use App\Core\Model;

class Ticket extends Model
{
    protected $table = 'tickets';
    public $id;
    public $event;
    public $user;
    public $ticket_type;
    public $price;
    public $qr_code;
    public $status;

    public function __construct($id = null, $event = new Event(), $user = new User(), $ticket_type = null, $price = null, $qr_code = null, $status=null)
    {
        $this->id = $id;
        $this->event = $event;
        $this->user = $user;
        $this->ticket_type = $ticket_type;
        $this->price = $price;
        $this->qr_code = $qr_code;
        $this->status = $status;
    }

    public static function getByUserId($userId){
        $db = Database::getInstance();

        $sql ="SELECT * FROM tickets WHERE user_id = :id ";
        $params=[':id'=>$userId];
        $stmt = $db->getConnection()->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        return self::toObjects($rows);
    }

    public static function getAll(){
        $db = Database::getInstance();

        $sql ="SELECT * FROM tickets ";
        $stmt = $db->getConnection()->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        return self::toObjects($rows);
    }
    public static function read($id)
    {
        $db = Database::getInstance();
        $sql = "SELECT * FROM tickets WHERE id = :id";
        $query = $db->getConnection()->prepare($sql);
        $query->bindParam(':id', $id);
        $query->execute();
        $row = $query->fetch();

        $ticket=null;
        if($row){
            $user = User::read($row['user_id']) ?? new User();
            $event = Event::read($row['event_id']) ?? new Event();
            $ticket = new Ticket($row['id'],$event,$user,$row['ticket_type'],$row['price'],$row['qr_code'],$row['status']);
        }
        return $ticket;
    } 
    private static function toObjects($rows)
    {
        $tickets = [];
        foreach ($rows as $row) {
            $user = User::read($row['user_id']) ?? new User();
            $event = Event::read($row['event_id']) ?? new Event();
            $tickets[] = new Ticket($row['id'],$event,$user,$row['ticket_type'],$row['price'],$row['qr_code'],$row['status']);
        }
        return $tickets;
    }
    public function save(): bool
    {
        $sql = "INSERT INTO tickets (event_id, user_id, ticket_type, price, qr_code, status) 
                VALUES (:event_id, :user_id, :ticket_type, :price, :qr_code, :status)";

        $db = Database::getInstance();
        $stmt = $db->getConnection()->prepare($sql);

        $params = [
            ':event_id' => $this->event->id,
            ':user_id' => $this->user->id,
            ':ticket_type' => $this->ticket_type,
            ':price' => $this->price,
            ':qr_code' => $this->qr_code,
            ':status' => $this->status,
        ];
    
        if ($stmt->execute($params)) {
            $this->id = $db->getConnection()->lastInsertId();
            return true;
        }
    
        return false;
    }

    public function delete(){
        $db = Database::getInstance();
        $sql="DELETE FROM tickets WHERE id = :id ";
        $params=['id'=>$this->id];
        $query=$db->getConnection()->prepare($sql);
        return $query->execute($params);
    }

    public static function updateStatus($ticket_id,$status){
        $db = Database::getInstance();
        $sql="UPDATE tickets SET status = :status WHERE id = :id ";
        $params=['id'=>$ticket_id , 'status'=> $status];
        $query=$db->getConnection()->prepare($sql);
        $query->execute($params);
    }
}
