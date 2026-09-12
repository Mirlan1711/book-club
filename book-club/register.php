<?php
require_once __DIR__ . '/auth.php';
requireGuest();

$errors = [];
$username = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirmation = $_POST['password_confirmation'] ?? '';

    if (mb_strlen($username) < 3 || mb_strlen($username) > 50) {
        $errors[] = 'Логин должен содержать от 3 до 50 символов.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Введите корректный email.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Пароль должен содержать минимум 8 символов.';
    }
    if ($password !== $passwordConfirmation) {
        $errors[] = 'Пароли не совпадают.';
    }

    if (!$errors) {
        $check = database()->prepare('SELECT id FROM users WHERE username = :username OR email = :email');
        $check->execute(['username' => $username, 'email' => $email]);
        if ($check->fetch()) {
            $errors[] = 'Этот логин или email уже занят.';
        }
    }

    if (!$errors) {
        $insert = database()->prepare(
            'INSERT INTO users (username, email, password_hash) VALUES (:username, :email, :password_hash)'
        );
        $insert->execute([
            'username' => $username,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);

        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) database()->lastInsertId();
        $_SESSION['flash'] = 'Регистрация прошла успешно. Добро пожаловать в клуб!';
        header('Location: dashboard.php');
        exit;
    }
}

$pageTitle = 'Регистрация';
require __DIR__ . '/header.php';
?>
<section class="card form-card">
    <h1>Регистрация</h1>
    <?php foreach ($errors as $error): ?>
        <p class="error"><?= e($error) ?></p>
    <?php endforeach; ?>
    <form method="post" novalidate>
        <label>Логин<input name="username" value="<?= e($username) ?>" required></label>
        <label>Email<input type="email" name="email" value="<?= e($email) ?>" required></label>
        <label>Пароль<input type="password" name="password" required></label>
        <label>Повторите пароль<input type="password" name="password_confirmation" required></label>
        <button class="button" type="submit">Зарегистрироваться</button>
    </form>
    <p>Уже есть аккаунт? <a href="login.php">Войдите</a>.</p>
</section>
<?php require __DIR__ . '/footer.php'; ?>
