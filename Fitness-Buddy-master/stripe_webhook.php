<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/vendor/autoload.php';

$config = require __DIR__ . '/stripe_config.php';

$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

try {
    $event = \Stripe\Webhook::constructEvent(
        $payload,
        $signature,
        $config['webhook_secret']
    );
} catch (\UnexpectedValueException $e) {
    error_log('Invalid Stripe webhook payload: ' . $e->getMessage());
    http_response_code(400);
    exit;
} catch (\Stripe\Exception\SignatureVerificationException $e) {
    error_log('Invalid Stripe webhook signature: ' . $e->getMessage());
    http_response_code(400);
    exit;
}

if ($event->type === 'checkout.session.completed') {
    $session = $event->data->object;

    $userId = (int) (
        $session->metadata->user_id ??
        $session->client_reference_id ??
        0
    );

    if (
        $userId > 0 &&
        $session->payment_status === 'paid' &&
        !empty($session->subscription)
    ) {
        $conn->beginTransaction();

        try {
            $stmt = $conn->prepare(
                "INSERT INTO stripe_subscriptions
                    (
                        user_id,
                        stripe_customer_id,
                        stripe_subscription_id,
                        subscription_status
                    )
                 VALUES
                    (
                        :user_id,
                        :customer_id,
                        :subscription_id,
                        'active'
                    )
                 ON DUPLICATE KEY UPDATE
                    stripe_customer_id = VALUES(stripe_customer_id),
                    stripe_subscription_id =
                        VALUES(stripe_subscription_id),
                    subscription_status = 'active'"
            );

            $stmt->execute([
                ':user_id' => $userId,
                ':customer_id' => $session->customer,
                ':subscription_id' => $session->subscription
            ]);

            $stmt = $conn->prepare(
                "UPDATE users
                 SET membership_tier = 'premium'
                 WHERE id = :user_id"
            );
            $stmt->execute([':user_id' => $userId]);

            $stmt = $conn->prepare(
                "UPDATE user_profiles
                 SET membership_tier = 'premium'
                 WHERE user_id = :user_id"
            );
            $stmt->execute([':user_id' => $userId]);

            $conn->commit();
        } catch (Throwable $e) {
            $conn->rollBack();
            error_log($e->getMessage());
            http_response_code(500);
            exit;
        }
    }
}

http_response_code(200);