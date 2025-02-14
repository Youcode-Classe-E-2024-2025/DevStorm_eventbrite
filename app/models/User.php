<?php

namespace App\models;


use App\Core\Database;
use App\Core\Model;
use App\Core\Session;
use App\enums\Role;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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

    public function getUserByEvent($event_id){
        $db = Database::getInstance();
        $stmt = $db->getConnection()->prepare("SELECT u.email FROM users as u JOIN events as e ON u.id = e.organizer_id WHERE e.id = :event_id");
        $stmt->bindParam(':event_id', $event_id, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function sendEmail(mixed $organizer_email,$status){
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'abdelmouhaimineeljassimi@gmail.com';
            $mail->Password = 'uzjs cpsr aasv ccba';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('abdelmouhaimineeljassimi@gmail.com', 'EventHive');
            $mail->addAddress($organizer_email, 'Recipient Name');
            $mail->isHTML(true);
            $mail->Subject = 'EVENT HIVE ACCOUNT STATUS';
            $bodyContent = '
<html lang="en">
<head>
    <meta charset="UTF-8">
</head>
<body style="background-color: #f3f4f6; font-family: \'Manrope\', sans-serif; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <div style="background-color: #8B5CF6; color: #ffffff; padding: 20px; text-align: center; font-family: \'Clash Display\', sans-serif;">
            <h1 style="font-size: 24px; font-weight: bold; margin: 0;">Event Hive</h1>
        </div>
        <div style="padding: 20px; color: #333333;">
            <h2 style="font-size: 20px; font-weight: bold; margin-bottom: 15px;">Account Status Update</h2>
            <p style="margin-bottom: 15px;">Dear User,</p>
            <p style="margin-bottom: 15px;">Your Event Hive account status has been updated. Your new status is:</p>
            <div style="color: #8B5CF6; font-size: 18px; font-weight: bold; margin-bottom: 15px;">'.htmlspecialchars($status).'</div>
            <p>If you have any questions about this change, please contact our support team.</p>
        </div>
        <div style="background-color: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280;">
            &copy; 2025 Event Hive. All rights reserved.
        </div>
    </div>
</body>
</html>';
            $mail->Body    = $bodyContent;

            $mail->AltBody = 'This is the plain-text version of the email.';
            $mail->SMTPDebug = 0;

            if ($mail->send()) {
                $alertMessage = "Message has been sent successfully!";
            } else {
                $alertMessage = "Message could not be sent.";
            }
        } catch (Exception $e) {
            $alertMessage = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}
