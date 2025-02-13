<?php

namespace App\models;

use App\Core\Database;

class Payment
{

    /**
     * Store a new payment in the database.
     *
     * @param array $data Payment data (ticket_id, payment_method, amount, payment_status, transaction_id)
     */
    public function storePayment(array $data)
    {
        try {
            $db = Database::getInstance();
            $sql = " INSERT INTO payments (
                    ticket_id, payment_method, amount, payment_status, transaction_id)
                     VALUES ( :ticket_id, :payment_method, :amount, :payment_status, :transaction_id ) ";
            $query = $db->getConnection()->prepare($sql);
            $params = [
                'ticket_id' => $data['ticket_id'],
                'payment_method' => $data['payment_method'],
                'amount' => $data['amount'],
                'payment_status' => $data['payment_status'],
                'transaction_id' => $data['transaction_id'],
            ];
            $query->execute($params);

            return true; // Success
        } catch (\PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false; // Failure
        }
    }

    /**
     * Retrieve a payment by its transaction ID.
     *
     * @param string $transactionId Transaction ID of the payment
     * @return array|null Payment data or null if not found
     */
    public function getPaymentByTransactionId(string $transactionId)
    {
        try {
            $db = Database::getInstance();
            $sql = "SELECT * FROM payments WHERE transaction_id = :transaction_id";
            $params =  ['transaction_id' => $transactionId];
            $query = $db->getConnection()->prepare($sql);
            $query->execute($params);
            $result = $query->fetch();
            return $result ?: null;
        } catch (\PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return null;
        }
    }
}
