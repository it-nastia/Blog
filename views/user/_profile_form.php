<?php

/** @var yii\web\View $this */
/** @var app\models\User $user */
/** @var app\models\ProfileForm $model */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
?>

<div class="profile-form">
    <div class="row">
        <!-- Аватар і основна інформація -->
        <div class="col-lg-4 col-md-5 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <?= Html::img(
                            $user->getAvatarUrl(),
                            [
                                'alt' => Html::encode($user->username),
                                'class' => 'rounded-circle',
                                'id' => 'avatar-preview',
                                'style' => 'width: 150px; height: 150px; object-fit: cover; border: 3px solid var(--border-neon);'
                            ]
                        ) ?>
                    </div>
                    <h4><?= Html::encode($user->username) ?></h4>
                    <p class="text-muted mb-2">
                        <span class="badge bg-secondary"><?= Html::encode(ucfirst($user->role)) ?></span>
                    </p>
                    <p class="text-muted small mb-0">
                        Member since <?= date('F Y', $user->created_at) ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Форма редагування -->
        <div class="col-lg-8 col-md-7">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Edit Profile</h5>
                </div>
                <div class="card-body">
                    <?php $form = ActiveForm::begin([
                        'id' => 'profile-form',
                        'action' => ['profile', 'id' => $user->id],
                        'method' => 'post'
                    ]); ?>

                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'username')->textInput([
                                'maxlength' => true,
                                'placeholder' => 'Enter username',
                                'id' => 'profileform-username'
                            ]) ?>
                            <div id="username-hint" class="form-text" style="display: none;">
                                Username must be at least 3 characters and can only contain letters, numbers and underscores.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <?= $form->field($model, 'email')->textInput([
                                'maxlength' => true,
                                'type' => 'email'
                            ]) ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <?= $form->field($model, 'avatar')->textInput([
                                'maxlength' => true,
                                'placeholder' => 'Enter avatar URL (optional)'
                            ])->hint('Enter URL of your avatar image. Leave empty to use default avatar.') ?>
                        </div>
                    </div>

                    <hr class="my-4">

                    <h6 class="mb-3">Change Password <small class="text-muted">(optional)</small></h6>
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <?= $form->field($model, 'current_password')->passwordInput([
                                'placeholder' => 'Enter current password'
                            ]) ?>
                        </div>

                        <div class="col-md-12 mb-3">
                            <?= $form->field($model, 'new_password')->passwordInput([
                                'placeholder' => 'Enter new password'
                            ]) ?>
                        </div>

                        <div class="col-md-12">
                            <?= $form->field($model, 'confirm_password')->passwordInput([
                                'placeholder' => 'Confirm new password'
                            ]) ?>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <small>Leave password fields empty if you don't want to change your password.</small>
                    </div>

                    <div class="form-group mt-4">
                        <?= Html::submitButton('Save Changes', [
                            'class' => 'btn btn-primary',
                            'name' => 'save-button'
                        ]) ?>
                        <?= Html::a('Cancel', ['profile', 'id' => $user->id], [
                            'class' => 'btn btn-secondary'
                        ]) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                    
                    <script>
                    // Оновлення прев'ю аватара при введенні URL
                    document.addEventListener('DOMContentLoaded', function() {
                        const avatarInput = document.querySelector('#profileform-avatar');
                        const avatarPreview = document.getElementById('avatar-preview');
                        
                        if (avatarInput && avatarPreview) {
                            avatarInput.addEventListener('input', function() {
                                const url = this.value.trim();
                                if (url) {
                                    avatarPreview.src = url;
                                    avatarPreview.onerror = function() {
                                        // Якщо зображення не завантажилось, використовуємо дефолтне
                                        this.src = '<?= addslashes($user->getAvatarUrl()) ?>';
                                    };
                                } else {
                                    avatarPreview.src = '<?= addslashes($user->getAvatarUrl()) ?>';
                                }
                            });
                        }
                        
                        // Показувати підказку для username тільки при редагуванні
                        const usernameInput = document.getElementById('profileform-username');
                        const usernameHint = document.getElementById('username-hint');
                        const originalUsername = usernameInput ? usernameInput.value : '';
                        
                        if (usernameInput && usernameHint) {
                            // Показувати підказку при фокусі, якщо значення змінилось
                            usernameInput.addEventListener('focus', function() {
                                if (this.value !== originalUsername) {
                                    usernameHint.style.display = 'block';
                                }
                            });
                            
                            // Показувати підказку при введенні, якщо значення відрізняється від початкового
                            usernameInput.addEventListener('input', function() {
                                if (this.value !== originalUsername) {
                                    usernameHint.style.display = 'block';
                                } else {
                                    usernameHint.style.display = 'none';
                                }
                            });
                            
                            // Приховувати підказку при втраті фокусу, якщо значення не змінилось
                            usernameInput.addEventListener('blur', function() {
                                if (this.value === originalUsername) {
                                    usernameHint.style.display = 'none';
                                }
                            });
                        }
                    });
                    </script>
                </div>
            </div>

            <!-- Кнопка виходу -->
            <div class="card mt-3">
                <div class="card-body">
                    <h6 class="mb-3">Account Actions</h6>
                    <?= Html::beginForm(['/site/logout'], 'post', ['class' => 'd-inline']) ?>
                        <?= Html::submitButton('Logout', [
                            'class' => 'btn btn-outline-danger',
                            'data' => [
                                'confirm' => 'Are you sure you want to logout?'
                            ]
                        ]) ?>
                    <?= Html::endForm() ?>
                </div>
            </div>
        </div>
    </div>
</div>

