<?php

namespace App\Models;


use App\Core\Database;
use App\Core\model;
use App\Core\Session;

session_start();

class User extends Model {
    protected $table = 'users';
    public $username;
    public $email;
    public $password;
    public $role;

    public static function findByEmail($email){
        $db = Database::getInstance();
        $query = $db->getConnection()->prepare("SELECT * FROM " . (new static())->getTable() . " WHERE email = :email");
        $query->bindParam(':email', $email);
        $query->execute();
        return $query->fetchObject('App\Models\User');
    }

    public function save()
    {
        $db = Database::getInstance();
        $query = $db->getConnection()->prepare("INSERT INTO " . $this->getTable() . " (username, email, password, role_id) VALUES (:username, :email, :password, :role)");
        $query->bindParam(':username', $this->username);
        $query->bindParam(':email', $this->email);
        $query->bindParam(':password', $this->password);
        $query->bindParam(':role', $this->role);
        
        if ($query->execute()) {
            return $db->getConnection()->lastInsertId();
        } else {
            return false;
        }
    }

    public function login()
    {
        $db = Database::getInstance();
        $query = $db->getConnection()->prepare("SELECT * FROM " . $this->getTable() . " WHERE email = :email");
        
        $query->bindParam(':email', $this->email);
        $query->execute();
        $user = $query->fetch();
        
        if ($user && password_verify($this->password, $user['password'])) {
            $session = new Session();
            $session->set('user_role', $user["role_id"]);
            return $user;
        }
        return false;

    }

    public function fetchAll(){
        $db = Database::getInstance();
        $query = $db->getConnection()->prepare("SELECT * FROM " . $this->getTable());
        $query->execute();
        return $query->fetchAll();
    }
    
}
