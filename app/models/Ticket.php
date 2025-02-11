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

    public function __construct($id = null, $event = new Event(), $user = new User(), $ticket_type = null, $price = null, $qr_code = null, $status)
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


}
