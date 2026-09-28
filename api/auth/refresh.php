<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/api.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    jsonResponse(['error' => 'Method not allowed.'], 405);
}

$oldToken = $_COOKIE[REFRESH_COOKIE_NAME] ?? '';
if ($oldToken === '') {
    jsonResponse(['error' => 'Refresh token is required.'], 401);
}

$pdo = database();
$pdo->beginTransaction();

try {
    $statement = $pdo->prepare(
        'SELECT rt.id AS refresh_id, rt.user_id, u.id, u.username, u.email, u.created_at '
        . 'FROM refresh_tokens rt JOIN users u ON u.id = rt.user_id '
        . 'WHERE rt.token_hash = :token_hash AND rt.revoked_at IS NULL AND rt.expires_at > UTC_TIMESTAMP() '
        . 'FOR UPDATE'
    );
    $statement->execute(['token_hash' => refreshTokenHash($oldToken)]);
    $row = $statement->fetch();

    if (!$row) {
        $pdo->rollBack();
        clearRefreshCookie();
        jsonResponse(['error' => 'Invalid or expired refresh token.'], 401);
    }

    $newToken = createRefreshToken();
    $revoke = $pdo->prepare('UPDATE refresh_tokens SET revoked_at = UTC_TIMESTAMP() WHERE id = :id');
    $revoke->execute(['id' => $row['refresh_id']]);
    storeRefreshToken($pdo, (int) $row['user_id'], $newToken);
    $pdo->commit();
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    throw $exception;
}

issueRefreshCookie($newToken);
jsonResponse([
    'access_token' => createAccessToken($row),
    'token_type' => 'Bearer',
    'expires_in' => ACCESS_TOKEN_TTL,
]);
