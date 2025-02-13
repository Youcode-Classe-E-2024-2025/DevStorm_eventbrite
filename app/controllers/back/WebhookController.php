<?php
namespace App\controllers\back;

use App\Core\Controller;
use Dotenv\Dotenv;
use App\models\Payment;

class WebhookController extends Controller
{
    private $paymentModel;

    public function __construct()
    {
        // Load environment variables
        $dotenv = Dotenv::createImmutable(__DIR__ . "/../../../");
        $dotenv->load();

        // Initialize the Payment model
        $this->paymentModel = new Payment();
    }

    /**
     * Handle incoming webhook events from Stripe.
     */
    public function handleWebhook()
    {
        // Retrieve Stripe secret key and webhook signing secret
        $stripeSecretKey = $_ENV['STRIPE_SECRET_KEY'] ?? null;
        $stripeWebhookSecret = $_ENV['STRIPE_WEBHOOK_SECRET'] ?? null;

        if (!$stripeSecretKey || !$stripeWebhookSecret) {
            http_response_code(500);
            echo json_encode(['error' => 'Missing required environment variables']);
            return;
        }

        // Set Stripe API key
        \Stripe\Stripe::setApiKey($stripeSecretKey);

        // Get the raw payload and signature from the request
        $payload = @file_get_contents('php://input');
        $sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? null;

        try {
            // Verify the webhook signature
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sigHeader,
                $stripeWebhookSecret
            );

            // Handle the event based on its type
            switch ($event->type) {
                case 'checkout.session.completed':
                    $this->handlePaymentSuccess($event->data->object);
                    break;
                default:
                    // Ignore other event types
                    http_response_code(200);
                    echo "Unhandled event type: " . $event->type;
            }
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            http_response_code(400);
            echo 'Invalid payload';
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            http_response_code(400);
            echo 'Invalid signature';
        }
    }

    /**
     * Handle successful payment logic.
     *
     * @param object $session The Stripe Checkout session object.
     */
    private function handlePaymentSuccess($session)
    {
        // Extract relevant data from the session object
        $paymentData = [
            'payment_method' => 'stripe', // Always 'stripe' for this integration
            'amount' => $session->amount_total / 100, // Convert amount from cents to dollars
            'transaction_id' => $session->payment_intent, // Payment intent ID
            'ticket_id' => $session->metadata->ticket_id ?? null, 
            'payment_status' => 'réussi', // Payment is successful
        ];

        // Delegate payment storage to the Payment model
        if ($this->paymentModel->storePayment($paymentData)) {
            http_response_code(200);
            echo "Payment recorded successfully";
        } else {
            http_response_code(500);
            echo "Failed to record payment";
        }
    }
}