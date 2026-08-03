<?php

declare(strict_types=1);

/**
 * This file assumes session_start() has already been called.
 */

function csrf_token(): string
{
    if (
        empty($_SESSION['csrf_token']) ||
        !is_string($_SESSION['csrf_token'])
    ) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function submitted_csrf_token(): string
{
    // Standard HTML form submission
    if (isset($_POST['csrf_token'])) {
        return (string) $_POST['csrf_token'];
    }

    // JavaScript fetch/AJAX request
    if (isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
        return (string) $_SERVER['HTTP_X_CSRF_TOKEN'];
    }

    return '';
}

function csrf_is_valid(): bool
{
    $submittedToken = submitted_csrf_token();
    $sessionToken = $_SESSION['csrf_token'] ?? '';

    if (
        $submittedToken === '' ||
        !is_string($sessionToken) ||
        $sessionToken === ''
    ) {
        return false;
    }

    return hash_equals($sessionToken, $submittedToken);
}

function require_csrf_token(bool $jsonResponse = false): void
{
    if (csrf_is_valid()) {
        return;
    }

    http_response_code(403);

    if ($jsonResponse) {
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode([
            'error' => 'Invalid or expired security token. Refresh the page and try again.'
        ]);
    } else {
        echo 'Invalid or expired security token. Refresh the page and try again.';
    }

    exit;
}