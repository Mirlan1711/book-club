<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/jwt.php';

function jsonResponse(array $body, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    exit;
}

function readJsonBody(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') {
        jsonResponse(['error' => 'Request body must be valid JSON.'], 400);
    }

    try {
        $body = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        jsonResponse(['error' => 'Request body must be valid JSON.'], 400);
    }

    if (!is_array($body)) {
        jsonResponse(['error' => 'Request body must be a JSON object.'], 400);
    }

    return $body;
}

function issueRefreshCookie(string $token): void
{
    setcookie(REFRESH_COOKIE_NAME, $token, [
        'expires' => time() + REFRESH_TOKEN_TTL,
        'path' => refreshCookiePath(),
        'secure' => isHttpsRequest(),
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
}

function clearRefreshCookie(): void
{
    setcookie(REFRESH_COOKIE_NAME, '', [
        'expires' => time() - 3600,
        'path' => refreshCookiePath(),
        'secure' => isHttpsRequest(),
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
}

function isHttpsRequest(): bool
{
    return ($_SERVER['HTTPS'] ?? '') !== '' && strtolower((string) $_SERVER['HTTPS']) !== 'off';
}

function bearerToken(): string
{
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    if ($header === '' && function_exists('getallheaders')) {
        $headers = getallheaders();
        $header = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    }
    if (!preg_match('/^Bearer\s+(.+)$/i', $header, $matches)) {
        jsonResponse(['error' => 'Authentication required.'], 401);
    }

    return trim($matches[1]);
}

function authenticatedUser(): array
{
    try {
        $claims = verifyAccessToken(bearerToken());
    } catch (Throwable) {
        jsonResponse(['error' => 'Invalid or expired token.'], 401);
    }

    $statement = database()->prepare(
        'SELECT id, username, email, created_at FROM users WHERE id = :id'
    );
    $statement->execute(['id' => $claims['sub']]);
    $user = $statement->fetch();

    if (!$user) {
        jsonResponse(['error' => 'User not found.'], 401);
    }

    return ['user' => $user, 'claims' => $claims];
}

function storeRefreshToken(PDO $pdo, int $userId, string $token): void
{
    $statement = $pdo->prepare(
        'INSERT INTO refresh_tokens (user_id, token_hash, expires_at) VALUES (:user_id, :token_hash, UTC_TIMESTAMP() + INTERVAL 30 DAY)'
    );
    $statement->execute([
        'user_id' => $userId,
        'token_hash' => refreshTokenHash($token),
    ]);
}

function revokeRefreshToken(PDO $pdo, string $token): void
{
    $statement = $pdo->prepare(
        'UPDATE refresh_tokens SET revoked_at = UTC_TIMESTAMP() WHERE token_hash = :token_hash AND revoked_at IS NULL'
    );
    $statement->execute(['token_hash' => refreshTokenHash($token)]);
}
