<?php require_once __DIR__ . '/auth.php'; ?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? 'Книжный клуб') ?></title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="site-header">
    <a class="logo" href="index.php">Книжный клуб</a>
    <nav>
        <?php if (currentUser()): ?>
            <a href="dashboard.php">Личный кабинет</a>
            <form class="logout-form" action="logout.php" method="post">
                <button type="submit">Выйти</button>
            </form>
        <?php else: ?>
            <a href="register.php">Регистрация</a>
            <a href="login.php">Войти</a>
        <?php endif; ?>
    </nav>
</header>
<main class="container">
    <?php if ($message = flash()): ?>
        <p class="notice"><?= e($message) ?></p>
    <?php endif; ?>
