<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/api.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    jsonResponse(['error' => 'Method not allowed.'], 405);
}

$body = readJsonBody();
$login = trim((string) ($body['login'] ?? $body['email'] ?? ''));
$password = (string) ($body['password'] ?? '');

if ($login === '' || $password === '') {
    jsonResponse(['error' => 'Login and password are required.'], 400);
}

$statement = database()->prepare(
    'SELECT id, username, email, password_hash, created_at FROM users WHERE username = :username OR email = :email LIMIT 1'
);
$statement->execute(['username' => $login, 'email' => $login]);
$user = $statement->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    jsonResponse(['error' => 'Invalid credentials.'], 401);
}

$refreshToken = createRefreshToken();
storeRefreshToken(database(), (int) $user['id'], $refreshToken);
issueRefreshCookie($refreshToken);

jsonResponse([
    'access_token' => createAccessToken($user),
    'token_type' => 'Bearer',
    'expires_in' => ACCESS_TOKEN_TTL,
]);
