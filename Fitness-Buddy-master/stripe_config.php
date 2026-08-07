<?php
require_once __DIR__ . '/load_env.php';

function env_required(string $key): string
{
    $value = getenv($key);
    if ($value === false || $value === '') {
        throw new RuntimeException("Missing required environment variable: {$key}. Check your .env file.");
    }
    return $value;
}

return [
    'secret_key'         => env_required('STRIPE_SECRET_KEY'),
    'webhook_secret'      => env_required('STRIPE_WEBHOOK_SECRET'),
    'monthly_price_id'    => env_required('STRIPE_MONTHLY_PRICE_ID'),
    'annual_price_id'     => env_required('STRIPE_ANNUAL_PRICE_ID'),
    'app_url'             => getenv('APP_URL') ?: 'http://localhost/fitness_buddy',
];
