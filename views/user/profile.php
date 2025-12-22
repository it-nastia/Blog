<?php

/** @var yii\web\View $this */
/** @var app\models\User $user */
/** @var app\models\ProfileForm $model */
/** @var array $stats */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Profile: ' . Html::encode($user->username);
$this->params['breadcrumbs'][] = ['label' => 'Profile', 'url' => ['profile', 'id' => $user->id]];
?>

<div class="user-profile">
    <?php if ($user->isAuthor()): ?>
        <!-- Layout для автора с боковой панелью -->
        <div class="row">
            <!-- Боковая панель (слева) -->
            <div class="col-lg-3 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Admin Panel</h5>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="#profile-section" class="list-group-item list-group-item-action active" data-section="profile">
                            Profile
                        </a>
                        <a href="#articles-section" class="list-group-item list-group-item-action" data-section="articles">
                            Articles
                        </a>
                        <a href="#categories-section" class="list-group-item list-group-item-action" data-section="categories">
                            Categories
                        </a>
                        <a href="#tags-section" class="list-group-item list-group-item-action" data-section="tags">
                            Tags
                        </a>
                        <a href="#comments-section" class="list-group-item list-group-item-action" data-section="comments">
                            Comments
                        </a>
                    </div>
                </div>

                <!-- Статистика -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0">Statistics</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-2"><strong>Articles:</strong> <?= $stats['articles'] ?? 0 ?></p>
                        <p class="mb-2"><strong>Categories:</strong> <?= $stats['categories'] ?? 0 ?></p>
                        <p class="mb-2"><strong>Tags:</strong> <?= $stats['tags'] ?? 0 ?></p>
                        <p class="mb-0"><strong>Comments:</strong> <?= $stats['comments'] ?? 0 ?></p>
                    </div>
                </div>
            </div>

            <!-- Основной контент (справа) -->
            <div class="col-lg-9">
                <!-- Секция Профиль -->
                <div id="profile-section" class="profile-section">
                    <h2>Profile</h2>
                    <?= $this->render('_profile_form', ['user' => $user, 'model' => $model]) ?>
                </div>

                <!-- Секция Статьи -->
                <div id="articles-section" class="profile-section" style="display: none;">
                    <h2>Articles</h2>
                    <div class="card">
                        <div class="card-body">
                            <p>Articles management section. Content will be added later.</p>
                            <p class="text-muted">Here you will be able to manage all articles.</p>
                        </div>
                    </div>
                </div>

                <!-- Секция Категории -->
                <div id="categories-section" class="profile-section" style="display: none;">
                    <h2>Categories</h2>
                    <div class="card">
                        <div class="card-body">
                            <p>Categories management section. Content will be added later.</p>
                            <p class="text-muted">Here you will be able to manage all categories.</p>
                        </div>
                    </div>
                </div>

                <!-- Секция Теги -->
                <div id="tags-section" class="profile-section" style="display: none;">
                    <h2>Tags</h2>
                    <div class="card">
                        <div class="card-body">
                            <p>Tags management section. Content will be added later.</p>
                            <p class="text-muted">Here you will be able to manage all tags.</p>
                        </div>
                    </div>
                </div>

                <!-- Секция Комментарии -->
                <div id="comments-section" class="profile-section" style="display: none;">
                    <h2>Comments</h2>
                    <div class="card">
                        <div class="card-body">
                            <p>Comments management section. Content will be added later.</p>
                            <p class="text-muted">Here you will be able to manage all comments.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Layout для читателя (без боковой панели) -->
        <div class="row">
            <div class="col-12">
                <h2>Profile</h2>
                <?= $this->render('_profile_form', ['user' => $user, 'model' => $model]) ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
// Перемикання між секціями для авторів
document.addEventListener('DOMContentLoaded', function() {
    const menuItems = document.querySelectorAll('[data-section]');
    const sections = document.querySelectorAll('.profile-section');

    menuItems.forEach(function(item) {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetSection = this.getAttribute('data-section');
            
            // Приховуємо всі секції
            sections.forEach(function(section) {
                section.style.display = 'none';
            });
            
            // Показуємо вибрану секцію
            const section = document.getElementById(targetSection + '-section');
            if (section) {
                section.style.display = 'block';
            }
            
            // Оновлюємо активний пункт меню
            menuItems.forEach(function(menuItem) {
                menuItem.classList.remove('active');
            });
            this.classList.add('active');
        });
    });
});
</script>
