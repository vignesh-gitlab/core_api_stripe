<?php
require_once('stripe-php/init.php');
require_once('constants.php');

\Stripe\Stripe::setApiKey(STRIPE_API_SECRET_KEY); // Replace with your secret key

header('Content-Type: application/json');

$input = json_decode(file_get_contents("php://input"), true);
$amount = isset($input['amount']) ? (int)$input['amount'] : 0;

if ($amount < 100) {
    echo json_encode(['error' => 'Amount must be at least ₹1']);
    exit;
}

try {
    $intent = \Stripe\PaymentIntent::create([
        'amount' => $amount,
        'currency' => 'inr',
        'automatic_payment_methods' => ['enabled' => true],
        'description' => 'Custom amount payment',
        'shipping' => [
            'name' => 'Test User',
            'address' => [
                'line1' => '123 Test Lane',
                'city' => 'Mumbai',
                'postal_code' => '400001',
                'state' => 'MH',
                'country' => 'IN'
            ]
        ],
        'receipt_email' => 'customer@example.com'
    ]);

    echo json_encode(['clientSecret' => $intent->client_secret]);
} catch (\Stripe\Exception\ApiErrorException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
