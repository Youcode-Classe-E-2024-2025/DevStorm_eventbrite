<?php
namespace App\models;

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
    /**
 * Get all promotions for a specific event
 * @param int $eventId The event ID
 * @return array List of promotions
 */
public function getPromotionsByEvent()
{
    $db = \App\Core\Database::getInstance();
    $query = $db->getConnection()->prepare("
        SELECT * FROM promotions 
        ORDER BY created_at DESC
    ");
    
    $query->execute();
    return $query->fetchAll(PDO::FETCH_ASSOC);
}


}
