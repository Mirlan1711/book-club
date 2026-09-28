<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/api.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    jsonResponse(['error' => 'Method not allowed.'], 405);
}

$token = $_COOKIE[REFRESH_COOKIE_NAME] ?? '';
if ($token !== '') {
    revokeRefreshToken(database(), $token);
}

clearRefreshCookie();
jsonResponse(['message' => 'Logged out.']);
