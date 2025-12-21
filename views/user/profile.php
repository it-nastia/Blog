<?php

/** @var yii\web\View $this */
/** @var app\models\User $user */

use yii\bootstrap5\Html;

$this->title = 'User Profile: ' . Html::encode($user->username);
$this->params['breadcrumbs'][] = ['label' => 'Profile', 'url' => ['profile', 'id' => $user->id]];
?>

<div class="user-profile">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>User profile page. Content will be added later.</p>
    
    <div class="card">
        <div class="card-body">
            <p><strong>Username:</strong> <?= Html::encode($user->username) ?></p>
            <p><strong>Email:</strong> <?= Html::encode($user->email) ?></p>
            <p><strong>Role:</strong> <?= Html::encode($user->role) ?></p>
        </div>
    </div>
</div>

