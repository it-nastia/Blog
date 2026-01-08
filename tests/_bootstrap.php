<?php
define('YII_ENV', 'test');
defined('YII_DEBUG') or define('YII_DEBUG', true);

require_once __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';
require __DIR__ .'/../vendor/autoload.php';

// Ensure basic test users exist for unit tests
try {
    // Delay loading Yii app components until needed
    \Yii::setAlias('@tests', __DIR__);

    // create admin (id=100) if missing
    $admin = \app\models\User::findOne(['id' => 100]);
    if (!$admin) {
        $admin = new \app\models\User();
        $admin->id = 100;
        $admin->username = 'admin';
        $admin->email = 'admin@example.com';
        $admin->setPassword('admin');
        $admin->auth_key = 'test100key';
        $admin->role = 'author';
        $admin->status = \app\models\User::STATUS_ACTIVE;
        $admin->created_at = time();
        $admin->updated_at = time();
        $admin->save(false);
    }

    // If needed, ensure admin also resolves for token like "100-token" by having id 100 (handled in model)

    // create demo user if missing
    $demo = \app\models\User::findOne(['username' => 'demo']);
    if (!$demo) {
        $demo = new \app\models\User();
        $demo->username = 'demo';
        $demo->email = 'demo@example.com';
        $demo->setPassword('demo');
        $demo->auth_key = 'demo-auth-key';
        $demo->role = 'reader';
        $demo->status = \app\models\User::STATUS_ACTIVE;
        $demo->created_at = time();
        $demo->updated_at = time();
        $demo->save(false);
    }
} catch (\Throwable $e) {
    // If DB not available during certain tasks, ignore and continue; tests will show errors.
}
