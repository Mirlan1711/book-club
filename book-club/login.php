<?php
require_once __DIR__ . '/auth.php';
requireGuest();

$error = null;
$login = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    $statement = database()->prepare(
        'SELECT id, password_hash FROM users WHERE username = :username OR email = :email LIMIT 1'
    );
    $statement->execute(['username' => $login, 'email' => $login]);
    $user = $statement->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['flash'] = 'Вы успешно вошли в аккаунт.';
        header('Location: dashboard.php');
        exit;
    }

    $error = 'Неверный логин (или email) либо пароль.';
}

$pageTitle = 'Вход';
require __DIR__ . '/header.php';
?>
<section class="card form-card">
    <h1>Вход</h1>
    <?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
    <form method="post" novalidate>
        <label>Логин или email<input name="login" value="<?= e($login) ?>" required></label>
        <label>Пароль<input type="password" name="password" required></label>
        <button class="button" type="submit">Войти</button>
    </form>
    <p>Нет аккаунта? <a href="register.php">Зарегистрируйтесь</a>.</p>
</section>
<?php require __DIR__ . '/footer.php'; ?>
