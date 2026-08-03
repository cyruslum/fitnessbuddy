<?php
// creating checkout using Stripe API, replaces legacy payment system

declare(strict_types=1);

session_start();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/vendor/autoload.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'You must be logged in.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Invalid request method.']);
    exit;
}

$config = require __DIR__ . '/stripe_config.php';

if (empty($config['secret_key'])) {
    http_response_code(500);
    echo json_encode(['error' => 'Stripe is not configured.']);
    exit;
}

require_csrf_token(true);

$request = json_decode(file_get_contents('php://input'), true);
$billingPeriod = $request['billing_period'] ?? '';

$prices = [
    'monthly' => $config['monthly_price_id'],
    'annual' => $config['annual_price_id']
];

if (!isset($prices[$billingPeriod]) || empty($prices[$billingPeriod])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid billing period.']);
    exit;
}

$userId = (int) $_SESSION['user_id'];

$stmt = $conn->prepare(
    'SELECT id, email FROM users WHERE id = :user_id LIMIT 1'
);
$stmt->execute([':user_id' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(404);
    echo json_encode(['error' => 'User not found.']);
    exit;
}

try {
    $stripe = new \Stripe\StripeClient($config['secret_key']);

    $checkoutSession = $stripe->checkout->sessions->create([
        'mode' => 'subscription',
        'customer_email' => $user['email'],
        'client_reference_id' => (string) $userId,
        'line_items' => [[
            'price' => $prices[$billingPeriod],
            'quantity' => 1
        ]],
        'metadata' => [
            'user_id' => (string) $userId
        ],
        'success_url' =>
            $config['app_url'] .
            '/payment_success.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' =>
            $config['app_url'] . '/profileSetup.php?payment=cancelled'
    ]);

    echo json_encode(['checkout_url' => $checkoutSession->url]);
} catch (\Stripe\Exception\ApiErrorException $e) {
    error_log($e->getMessage());

    http_response_code(500);
    echo json_encode(['error' => 'Unable to start Stripe Checkout.']);
}