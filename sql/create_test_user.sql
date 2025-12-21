-- SQL скрипт для создания тестового пользователя (автора)
-- Выполните этот скрипт в phpMyAdmin

USE `filmblog`;

-- Создание тестового пользователя-автора
-- Username: admin
-- Email: admin@filmblog.com
-- Password: admin123
-- Role: author

-- ВАЖНО: Этот хеш пароля нужно сгенерировать через PHP!
-- Используйте PHP скрипт create_user.php вместо этого SQL файла
-- или выполните в консоли: php yii user/create admin admin@filmblog.com admin123 author

-- Пример SQL (хеш пароля "admin123"):
INSERT INTO `users` (`username`, `email`, `password_hash`, `auth_key`, `role`, `status`, `created_at`, `updated_at`) 
VALUES (
    'admin',
    'admin@filmblog.com',
    '$2y$13$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    CONCAT('test-auth-key-', UNIX_TIMESTAMP()),
    'author',
    1,
    UNIX_TIMESTAMP(),
    UNIX_TIMESTAMP()
);

