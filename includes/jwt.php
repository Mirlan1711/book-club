<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

function base64UrlEncode(string $value): string
{
    return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
}

function base64UrlDecode(string $value): string
{
    $padding = strlen($value) % 4;
    if ($padding > 0) {
        $value .= str_repeat('=', 4 - $padding);
    }

    $decoded = base64_decode(strtr($value, '-_', '+/'), true);
    if ($decoded === false) {
        throw new InvalidArgumentException('Invalid base64url value.');
    }

    return $decoded;
}

function createAccessToken(array $user): string
{
    $now = time();
    $header = ['typ' => 'JWT', 'alg' => JWT_ALGORITHM];
    $payload = [
        'sub' => (string) $user['id'],
        'iss' => jwtIssuer(),
        'aud' => jwtAudience(),
        'iat' => $now,
        'exp' => $now + ACCESS_TOKEN_TTL,
        'jti' => bin2hex(random_bytes(16)),
    ];

    $encodedHeader = base64UrlEncode((string) json_encode($header, JSON_THROW_ON_ERROR));
    $encodedPayload = base64UrlEncode((string) json_encode($payload, JSON_THROW_ON_ERROR));
    $signingInput = $encodedHeader . '.' . $encodedPayload;
    $signature = hash_hmac('sha256', $signingInput, accessTokenSecret(), true);

    return $signingInput . '.' . base64UrlEncode($signature);
}

function verifyAccessToken(string $token): array
{
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        throw new InvalidArgumentException('Invalid JWT format.');
    }

    [$encodedHeader, $encodedPayload, $encodedSignature] = $parts;
    $header = json_decode(base64UrlDecode($encodedHeader), true, 512, JSON_THROW_ON_ERROR);
    $payload = json_decode(base64UrlDecode($encodedPayload), true, 512, JSON_THROW_ON_ERROR);
    $signature = base64UrlDecode($encodedSignature);

    if (!is_array($header) || !is_array($payload)
        || ($header['alg'] ?? null) !== JWT_ALGORITHM
        || ($header['typ'] ?? null) !== 'JWT'
    ) {
        throw new InvalidArgumentException('Unsupported JWT header.');
    }

    $expected = hash_hmac('sha256', $encodedHeader . '.' . $encodedPayload, accessTokenSecret(), true);
    if (!hash_equals($expected, $signature)) {
        throw new InvalidArgumentException('Invalid JWT signature.');
    }

    if (($payload['iss'] ?? null) !== jwtIssuer() || ($payload['aud'] ?? null) !== jwtAudience()) {
        throw new InvalidArgumentException('Invalid JWT issuer or audience.');
    }

    if (!isset($payload['sub'], $payload['iat'], $payload['exp'], $payload['jti'])
        || !is_string($payload['sub'])
        || !is_int($payload['iat'])
        || !is_int($payload['exp'])
        || !is_string($payload['jti'])
        || $payload['exp'] <= time()
    ) {
        throw new InvalidArgumentException('Invalid or expired JWT claims.');
    }

    return $payload;
}

function createRefreshToken(): string
{
    return base64UrlEncode(random_bytes(32));
}

function refreshTokenHash(string $token): string
{
    return hash_hmac('sha256', $token, refreshTokenSecret());
}
