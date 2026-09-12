<?php
declare(strict_types=1);

require_once __DIR__ . '/session.php';
$_SESSION = [];
session_destroy();

session_start();
$_SESSION['flash'] = 'Вы вышли из аккаунта.';
header('Location: index.php');
exit;
