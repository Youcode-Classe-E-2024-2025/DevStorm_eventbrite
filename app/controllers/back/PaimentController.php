<?php

namespace App\controllers\back;

use App\Core\Controller;
use App\models\Ticket;
use Dotenv\Dotenv;
class PaimentController extends Controller
{

    public function checkout($id){
        $dotenv = Dotenv::createImmutable(__DIR__ . "/../../../");
        $dotenv->load();
        $item = Ticket::read($id);
        // var_dump($item);die;
        $stripeSecretKey = $_ENV['STRIPE_SECRET_KEY'] ?? null;
        $domain = $_ENV['DOMAIN'] ?? null;

        \Stripe\Stripe::setApiKey($stripeSecretKey);
        header('Content-Type: application/json');

        $checkout_session = \Stripe\Checkout\Session::create([
          'line_items' => [[
            'quantity' => 1,
                    'price_data' => [
                        'currency' => 'usd',
                        'unit_amount' => $item->price*100, // (e.g., $20.00 = 2000 cents)
                        'product_data' => [
                            'name' =>'Ticket for '. $item->event->title, 
                            'description' => $item->event->description , 
                        ],
                    ],
          ]],
          'mode' => 'payment',
          'success_url' => $domain . '/paiment/success',
          'cancel_url' => $domain . '/paiment/cancel',
          'metadata' => [ // Include ticket ID in metadata
            'ticket_id' => $item->id, 
           ],
        ]);
        
        header("HTTP/1.1 303 See Other");
        header("Location: " . $checkout_session->url);
    }
    public function paimentSuccess(){
        echo "Payment was successful!";
    }
    public function paimentCancel(){
        echo "Payment was canceled.";
    }
}

