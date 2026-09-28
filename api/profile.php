<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/api.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    jsonResponse(['error' => 'Method not allowed.'], 405);
}

$authenticated = authenticatedUser();
$user = $authenticated['user'];
$claims = $authenticated['claims'];

jsonResponse([
    'id' => (int) $user['id'],
    'username' => $user['username'],
    'email' => $user['email'],
    'created_at' => $user['created_at'],
    'iss' => $claims['iss'],
    'aud' => $claims['aud'],
    'jti' => $claims['jti'],
    'expires_at' => $claims['exp'],
]);
