<?php

declare(strict_types=1);

// Укажите параметры своей локальной базы через переменные среды, а не в коде.
const DB_HOST = '127.0.0.1';
const DB_NAME = 'book_club';
const DB_USER = 'root';

function dbPassword(): string
{
    return getenv('BOOK_CLUB_DB_PASSWORD') ?: '';
}
