# Книжный клуб

PHP-приложение с регистрацией, входом, личным кабинетом и выходом из сессии.

## Запуск

1. Запустите Apache и MySQL в панели XAMPP.
2. Откройте phpMyAdmin и импортируйте файл `database/schema.sql`.
3. В `config.php` укажите пользователя базы. Пароль передавайте переменной среды `BOOK_CLUB_DB_PASSWORD`; он не хранится в коде.
4. Скопируйте папку `book-club` в `C:\xampp\htdocs\` или настройте веб-сервер на эту папку.
5. Откройте в браузере `http://localhost/book-club/`.

## Проверка

- Зарегистрируйте нового пользователя.
- Повторите регистрацию с тем же логином или email.
- Проверьте вход с правильным и неправильным паролем.
- Откройте `dashboard.php` без входа.
- Выйдите из аккаунта и повторно откройте кабинет.

Пароли хешируются функцией `password_hash()` и проверяются `password_verify()`.

## JWT API

Импортируйте обновлённый `database/schema.sql`, затем задайте переменные среды:

```text
ACCESS_TOKEN_SECRET=<случайная строка минимум 32 символа>
REFRESH_TOKEN_SECRET=<другая случайная строка минимум 32 символа>
JWT_ISSUER=student-app
JWT_AUDIENCE=student-app-api
BOOK_CLUB_REFRESH_COOKIE_PATH=/book-club/
```

API-методы:

- `POST /book-club/api/auth/login.php` — JSON `{"login":"...","password":"..."}`; возвращает Access Token и устанавливает HttpOnly Refresh Token cookie.
- `GET /book-club/api/profile.php` — заголовок `Authorization: Bearer ACCESS_TOKEN`.
- `POST /book-club/api/auth/refresh.php` — ротирует Refresh Token cookie и выдаёт новый Access Token.
- `POST /book-club/api/auth/logout.php` — отзывает Refresh Token и удаляет cookie.

Access Token действует 15 минут и содержит только технические claims `sub`, `iss`, `aud`, `iat`, `exp`, `jti`. Refresh Token хранится в базе только в виде HMAC-SHA-256 хэша. Cookie автоматически получает `Secure` при HTTPS; для локального HTTP это отключается, иначе браузер не отправит cookie.

## Структура проекта

- корень `book-club/` — страницы сайта и публичные API-маршруты;
- `book-club/includes/` — общие модули конфигурации, базы данных, сессий, JWT и JSON API;
- `book-club/api/` — обработчики API-маршрутов;
- `book-club/assets/` — стили интерфейса;
- `book-club/database/` — SQL-схема базы данных;
- `book-club/storage/sessions/` — runtime-файлы PHP-сессий, исключённые из Git.
