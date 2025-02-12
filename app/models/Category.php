<?php
namespace App\models;

use App\Core\Model;
use App\Core\Database;
use PDO;

class Category extends Model
{
    protected $table = 'categories';
    public $id;
    public $name;
    
    public function __construct($id=null,$name=null)
    {
        $this->id = $id;
        $this->name = $name;
    }

    public function getAllCategories()
    {
        $db =Database::getInstance();
        $query = $db->getConnection()->prepare("SELECT * FROM categories ORDER BY name");
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function read($id)
    {
        $db = Database::getInstance();
        $sql = "SELECT * FROM categories WHERE id = :id";
        $query = $db->getConnection()->prepare($sql);
        $query->bindParam(':id', $id);
        $query->execute();
        $data = $query->fetch();
        $category=null;
        if($data){
            $category = new Category($data['id'],$data['name']);
           
        }
        return $category;
    }

    public function delete($id){
        $db = Database::getInstance();
        $sql = "DELETE FROM categories WHERE id = :id";
        $query = $db->getConnection()->prepare($sql);
        $query->bindParam(':id', $id);
        $query->execute();
    }


}
