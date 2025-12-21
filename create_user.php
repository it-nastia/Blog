<?php
/**
 * Скрипт для создания тестового пользователя
 * 
 * Использование:
 * 1. Убедитесь, что MySQL запущен и база данных filmblog создана
 * 2. Запустите: php create_user.php
 * 
 * Или используйте консольную команду:
 * php yii user/create admin admin@filmblog.com admin123 author
 */

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/config/console.php';

new yii\console\Application($config);

use app\models\User;

echo "Creating test user...\n";

// Проверка, существует ли пользователь
if (User::find()->where(['username' => 'admin'])->exists()) {
    echo "Error: User 'admin' already exists.\n";
    exit(1);
}

// Создание пользователя
$user = new User();
$user->username = 'admin';
$user->email = 'admin@filmblog.com';
$user->role = User::ROLE_AUTHOR;
$user->status = User::STATUS_ACTIVE;
$user->setPassword('admin123');
$user->generateAuthKey();

if ($user->save()) {
    echo "Success! User created:\n";
    echo "  ID: {$user->id}\n";
    echo "  Username: {$user->username}\n";
    echo "  Email: {$user->email}\n";
    echo "  Password: admin123\n";
    echo "  Role: {$user->role}\n";
    echo "  Status: " . ($user->status ? 'Active' : 'Inactive') . "\n";
    exit(0);
} else {
    echo "Error: Failed to create user.\n";
    foreach ($user->errors as $attribute => $errors) {
        foreach ($errors as $error) {
            echo "  - {$attribute}: {$error}\n";
        }
    }
    exit(1);
}

