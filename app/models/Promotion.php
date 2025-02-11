<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class Promotion extends Model
{
    protected $table = 'promotions';
    
    public function createPromotion($data)
    {
        $code = $this->generateUniqueCode();
        $db = \App\Core\Database::getInstance();
        
        $sql = "INSERT INTO promotions (code, discount_percentage, valid_from, valid_until) 
                VALUES (:code, :discount_percentage, :valid_from, :valid_until)";
                
        $stmt = $db->getConnection()->prepare($sql);
        return $stmt->execute([
            'code' => $code,
            'discount_percentage' => $data['discount_percentage'],
            'valid_from' => $data['valid_from'],
            'valid_until' => $data['valid_until']
        ]);
    }
    
    private function generateUniqueCode()
    {
        return strtoupper(uniqid('PROMO_'));
    }



}
