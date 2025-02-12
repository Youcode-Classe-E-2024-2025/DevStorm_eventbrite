<?php
namespace App\models;

use App\Core\Model;
use App\Core\Database;
use PDO;

class Tag extends Model
{
    protected $table = 'tags';
    public $id;
    public $name;

    public function __construct($id=null,$name=null)
    {
        $this->id = $id;
        $this->name = $name;
    }

    public function getAllTags()
    {
        $db =Database::getInstance();
        $query = $db->getConnection()->prepare("SELECT * FROM tags ORDER BY name");
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete($id){
        $db = Database::getInstance();
        $sql = "DELETE FROM tags WHERE id = :id";
        $query = $db->getConnection()->prepare($sql);
        $query->bindParam(':id', $id);
        $query->execute();
    }
    public function create(){
        $db = Database::getInstance();
        $sql = "INSERT INTO tags (name) VALUES (:name)";
        $query = $db->getConnection()->prepare($sql);
        $query->bindParam(':name',$this->name);
        $query->execute();
    }

}
