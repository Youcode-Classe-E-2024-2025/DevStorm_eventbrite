<?php

namespace App\models;


use App\Core\Database;
use App\Core\Model;
use App\Core\Session;
use App\enums\Role;


class User extends Model {
    protected $table = 'users';
    public $id;
    public $username;
    public $email;
    public $password;
    public  $role;

    public function __construct($name=null,$email=null,$password=null,$role = Role::PARTICIPANT)
    {
        $this->username = $name;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
    }
    public static function read($id)
    {
        $db = Database::getInstance();
        $sql = "SELECT * FROM users WHERE id = :id";
        $query = $db->getConnection()->prepare($sql);
        $query->bindParam(':id', $id);
        $query->execute();
        $data = $query->fetch();

        $user=null;
        if($data){
            $user = new User($data['name'],$data['email'],'',Role::from($data['role']));
            $user->id = $data['id'];
        }
        return $user;
    } 

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
        $query = $db->getConnection()->prepare("INSERT INTO " . $this->getTable() . " (name, email, password_hash, role) VALUES (:username, :email, :password, :role)");
        $query->bindParam(':username', $this->username);
        $query->bindParam(':email', $this->email);
        $query->bindParam(':password', $this->password);
        $role_name = $this->role->value;
        $query->bindParam(':role', $role_name);
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
        if ($user && password_verify($this->password, $user['password_hash'])) {
            Session::set('user_role', $user["role"]);
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

    function updateStatus($userId, $status) {
            $db = Database::getInstance();
            $stmt = $db->getConnection()->prepare("UPDATE users SET status = :status WHERE id = :id");
            $stmt->execute(["status" => $status, "id" => $userId]);
            $stmt->execute();
        }
    
}
