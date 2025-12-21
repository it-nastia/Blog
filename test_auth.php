<?php
/**
 * Скрипт для проверки авторизации
 * 
 * Использование: php test_auth.php
 * 
 * Проверяет:
 * 1. Существует ли пользователь в БД
 * 2. Правильно ли захеширован пароль
 * 3. Работает ли валидация пароля
 */

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/config/console.php';

new yii\console\Application($config);

use app\models\User;

echo "=== Testing Authentication ===\n\n";

// Получаем всех пользователей
$users = User::find()->all();

if (empty($users)) {
    echo "❌ No users found in database!\n";
    echo "Please create a user first.\n";
    exit(1);
}

echo "Found " . count($users) . " user(s):\n\n";

foreach ($users as $user) {
    echo "User ID: {$user->id}\n";
    echo "Username: {$user->username}\n";
    echo "Email: {$user->email}\n";
    echo "Role: {$user->role}\n";
    echo "Status: " . ($user->status ? 'Active' : 'Inactive') . "\n";
    echo "Password Hash: " . substr($user->password_hash, 0, 20) . "...\n";
    echo "Auth Key: " . ($user->auth_key ?: 'NOT SET') . "\n";
    
    // Проверка формата хеша пароля
    if (strpos($user->password_hash, '$2y$') === 0) {
        echo "✓ Password hash format: CORRECT (bcrypt)\n";
    } else {
        echo "✗ Password hash format: INCORRECT (should start with \$2y\$)\n";
        echo "  Current hash: {$user->password_hash}\n";
        echo "  WARNING: Password was not hashed using Yii2 security!\n";
    }
    
    // Тест валидации пароля (попробуем несколько вариантов)
    echo "\nTesting password validation:\n";
    
    $testPasswords = ['admin123', 'admin', 'password', '123456'];
    $found = false;
    
    foreach ($testPasswords as $testPassword) {
        if ($user->validatePassword($testPassword)) {
            echo "  ✓ Password '{$testPassword}': CORRECT\n";
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        echo "  ✗ None of the test passwords worked\n";
        echo "  Please check if password was hashed correctly.\n";
    }
    
    echo "\n" . str_repeat('-', 50) . "\n\n";
}

echo "\n=== Test Complete ===\n";
echo "\nTo test login in browser:\n";
echo "1. Go to: http://localhost/Blog/web/index.php?r=site/login\n";
echo "2. Try to login with your username and password\n";

