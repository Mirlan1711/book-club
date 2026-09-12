<?php
$pageTitle = 'Книжный клуб';
require __DIR__ . '/header.php';
?>
<section class="hero">
    <p class="eyebrow">Читаем и обсуждаем вместе</p>
    <h1>Книжный клуб для тех, кто любит хорошие истории</h1>
    <p>Присоединяйтесь к нашему сообществу читателей, находите вдохновение и открывайте новые книги.</p>
    <?php if (currentUser()): ?>
        <a class="button" href="dashboard.php">Открыть личный кабинет</a>
    <?php else: ?>
        <a class="button" href="register.php">Стать участником</a>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/footer.php'; ?>
