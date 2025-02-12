<?php
namespace App\models;

use App\Core\Model;
use PDO;

class Promotion extends Model
{
    protected $table = 'promotions';
    
    private function generateUniqueCode()
    {
        // Generate a unique code
        $prefix = 'PROMO_';
        $randomString = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 5);
        return $prefix . $randomString;
    }
   
    
    
}

