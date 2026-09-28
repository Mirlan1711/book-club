<?php

declare(strict_types=1);

// Укажите параметры своей локальной базы через переменные среды, а не в коде.
const DB_HOST = '127.0.0.1';
const DB_NAME = 'book_club';
const DB_USER = 'root';

const JWT_ALGORITHM = 'HS256';
const ACCESS_TOKEN_TTL = 900;
const REFRESH_TOKEN_TTL = 2592000;
const REFRESH_COOKIE_NAME = 'book_club_refresh';

function dbPassword(): string
{
    return getenv('BOOK_CLUB_DB_PASSWORD') ?: '';
}

function accessTokenSecret(): string
{
    return requireSecret('ACCESS_TOKEN_SECRET');
}

function refreshTokenSecret(): string
{
    return requireSecret('REFRESH_TOKEN_SECRET');
}

function jwtIssuer(): string
{
    return getenv('JWT_ISSUER') ?: 'student-app';
}

function jwtAudience(): string
{
    return getenv('JWT_AUDIENCE') ?: 'student-app-api';
}

function refreshCookiePath(): string
{
    return getenv('BOOK_CLUB_REFRESH_COOKIE_PATH') ?: '/book-club/';
}

function requireSecret(string $name): string
{
    $secret = getenv($name) ?: '';
    if (strlen($secret) < 32) {
        throw new RuntimeException($name . ' must contain at least 32 characters.');
    }

    return $secret;
}
