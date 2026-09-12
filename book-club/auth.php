<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/session.php';

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function currentUser(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    $statement = database()->prepare(
        'SELECT id, username, email, created_at FROM users WHERE id = :id'
    );
    $statement->execute(['id' => $_SESSION['user_id']]);
    $user = $statement->fetch();

    return $user ?: null;
}

function requireGuest(): void
{
    if (currentUser() !== null) {
        header('Location: dashboard.php');
        exit;
    }
}

function requireAuth(): array
{
    $user = currentUser();

    if ($user === null) {
        $_SESSION['flash'] = 'Для доступа к личному кабинету войдите в аккаунт.';
        header('Location: login.php');
        exit;
    }

    return $user;
}

function flash(): ?string
{
    $message = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);

    return $message;
}
