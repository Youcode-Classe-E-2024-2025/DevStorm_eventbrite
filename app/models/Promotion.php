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
    public function createPromotion($data)
    {
        $db = \App\Core\Database::getInstance();
        $connection = $db->getConnection();
        
        try {
            $connection->beginTransaction();
            
            // Insert into promotions table
            $code = $this->generateUniqueCode();
            $sql = "INSERT INTO promotions (code, discount_percentage, valid_from, valid_until) 
                    VALUES (:code, :discount_percentage, :valid_from, :valid_until) RETURNING id";
                    
            $stmt = $connection->prepare($sql);
            $stmt->execute([
                'code' => $code,
                'discount_percentage' => $data['discount_percentage'],
                'valid_from' => $data['valid_from'],
                'valid_until' => $data['valid_until']
            ]);
            
            $promotionId = $connection->lastInsertId();
            
            // Link promotion to event
            $sqlLink = "INSERT INTO event_promotions (event_id, promotion_id) VALUES (:event_id, :promotion_id)";
            $stmtLink = $connection->prepare($sqlLink);
            $stmtLink->execute([
                'event_id' => $data['event_id'],
                'promotion_id' => $promotionId
            ]);
            
            $connection->commit();
            return true;
            
        } catch (\Exception $e) {
            $connection->rollBack();
            return false;
        }
    }
    
    
}

