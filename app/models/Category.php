<?php
namespace App\models;

use App\Core\Model;
use PDO;

class Category extends Model
{
    protected $table = 'categories';

    public function getAllCategories()
    {
        $db = \App\Core\Database::getInstance();
        $query = $db->getConnection()->prepare("SELECT * FROM categories ORDER BY name");
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
}
