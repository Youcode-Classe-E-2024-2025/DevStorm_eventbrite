<?php

namespace App\models;


use App\Core\Database;
use App\Core\Model;
use App\Core\Session;
use App\enums\Role;
use PDO;


class User extends Model {
    protected $table = 'users';
    public $id;
    public $username;

    public $email;
    public $password;
    public $role;
    public $avatar;
    public $status;

    public function __construct($id=null,$name=null,$email=null,$password=null,$role = Role::PARTICIPANT,$avatar=null,$status=null)
    {
        $this->id=$id;
        $this->username = $name;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
        $this->avatar = $avatar;
        $this->status = $status;
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
            $user = new User($data['id'],$data['name'],$data['email'],'',Role::from($data['role']),$data['avatar'],$data['status']);
        }
        return $user;
    }

    public function save()
    {
        $db = Database::getInstance();
        $query = $db->getConnection()->prepare("INSERT INTO " . $this->getTable() . " (name, email, password_hash, role, avatar) VALUES (:username, :email, :password, :role, :avatar)");
        $query->bindParam(':username', $this->username);
        $query->bindParam(':email', $this->email);
        $query->bindParam(':password', $this->password);
        $role_name = $this->role->value;
        $avatar = $this->avatar ?? 'empty';
        $query->bindParam(':role', $role_name);
        $query->bindParam(':avatar', $avatar);
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

    /**
     * create  objects
     *
     * @param array $rows
     * @return array
     */
    private static function toObjects($rows){
        $users=[];
        foreach ($rows as $row) {
            $users[] =  new User($row['id'],$row['name'],$row['email'],'',Role::from($row['role']),$row['avatar'],$row['status']);
        }
        return $users;
    }

    public function fetchAll(){
        $db = Database::getInstance();
        $query = $db->getConnection()->prepare("SELECT * FROM " . $this->getTable());
        $query->execute();
        $rows = $query->fetchAll();
        return self::toObjects($rows);
    }

    public function updateStatus($userId, $status) {
        $db = Database::getInstance();
        $stmt = $db->getConnection()->prepare("UPDATE users SET status = :status WHERE id = :id");
        $stmt->execute(["status" => $status, "id" => $userId]);
        $stmt->execute();
    }
    public function getUserByEmail($email){
        $db = Database::getInstance();
        $stmt = $db->getConnection()->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }
}
