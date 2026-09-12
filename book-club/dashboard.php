<?php
require_once __DIR__ . '/auth.php';
$user = requireAuth();
$pageTitle = 'Личный кабинет';
require __DIR__ . '/header.php';
?>
<section class="card dashboard">
    <p class="eyebrow">Личный кабинет</p>
    <h1>Здравствуйте, <?= e($user['username']) ?>!</h1>
    <dl>
        <div><dt>Идентификатор</dt><dd><?= e((string) $user['id']) ?></dd></div>
        <div><dt>Логин</dt><dd><?= e($user['username']) ?></dd></div>
        <div><dt>Email</dt><dd><?= e($user['email']) ?></dd></div>
        <div><dt>Дата регистрации</dt><dd><?= e(date('d.m.Y H:i', strtotime($user['created_at']))) ?></dd></div>
    </dl>
</section>
<?php require __DIR__ . '/footer.php'; ?>
