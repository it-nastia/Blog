<?php
/**
 * Скрипт для исправления пароля пользователя
 * 
 * Использование через браузер:
 * http://localhost/Blog/fix_user_password.php?username=admin&password=newpassword
 * 
 * Или через консоль:
 * php fix_user_password.php admin newpassword
 */

// Определяем, запущен ли скрипт через браузер или консоль
$isWeb = php_sapi_name() !== 'cli';

if ($isWeb) {
    // Запуск через браузер
    require __DIR__ . '/vendor/autoload.php';
    require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';
    
    $config = require __DIR__ . '/config/web.php';
    new yii\web\Application($config);
    
    $username = $_GET['username'] ?? 'admin';
    $password = $_GET['password'] ?? 'admin123';
    
    echo "<h2>Fix User Password</h2>";
    echo "<p>Updating password for user: <strong>{$username}</strong></p>";
} else {
    // Запуск через консоль
    require __DIR__ . '/vendor/autoload.php';
    require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';
    
    $config = require __DIR__ . '/config/console.php';
    new yii\console\Application($config);
    
    $username = $argv[1] ?? 'admin';
    $password = $argv[2] ?? 'admin123';
    
    echo "=== Fix User Password ===\n";
    echo "Username: {$username}\n";
    echo "New Password: {$password}\n\n";
}

use app\models\User;

$user = User::find()->where(['username' => $username])->one();

if (!$user) {
    $message = "Error: User '{$username}' not found!";
    if ($isWeb) {
        echo "<p style='color: red;'>{$message}</p>";
    } else {
        echo "{$message}\n";
    }
    exit(1);
}

// Обновляем пароль
$user->setPassword($password);
$user->generateAuthKey();

if ($user->save(false)) {
    $message = "✓ Success! Password updated for user '{$username}'";
    if ($isWeb) {
        echo "<p style='color: green;'>{$message}</p>";
        echo "<p><a href='/Blog/web/index.php?r=site/login'>Go to Login Page</a></p>";
    } else {
        echo "{$message}\n";
        echo "You can now login with:\n";
        echo "  Username: {$username}\n";
        echo "  Password: {$password}\n";
    }
    exit(0);
} else {
    $message = "✗ Error: Failed to update password.";
    if ($isWeb) {
        echo "<p style='color: red;'>{$message}</p>";
        echo "<pre>";
        print_r($user->errors);
        echo "</pre>";
    } else {
        echo "{$message}\n";
        foreach ($user->errors as $attribute => $errors) {
            foreach ($errors as $error) {
                echo "  - {$attribute}: {$error}\n";
            }
        }
    }
    exit(1);
}

