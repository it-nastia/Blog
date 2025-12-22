<?php

/** @var yii\web\View $this */
/** @var app\models\User $user */
/** @var app\models\ProfileForm $model */
/** @var array $stats */
/** @var app\models\Category $categoryModel */

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
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2>Categories</h2>
                        <button type="button" class="btn btn-success" onclick="toggleCategoryForm()">
                            <i class="bi bi-plus-circle"></i> Create Category
                        </button>
                    </div>

                    <!-- Форма создания категории -->
                    <div id="category-create-form" class="card mb-4" style="display: none;">
                        <div class="card-header">
                            <h5 class="mb-0">Create New Category</h5>
                        </div>
                        <div class="card-body">
                            <?php
                            $createCategoryModel = new \app\models\Category();
                            $form = ActiveForm::begin([
                                'action' => ['user/category-create'],
                                'method' => 'post',
                                'id' => 'category-create-form-element'
                            ]);
                            ?>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <?= $form->field($createCategoryModel, 'name')->textInput(['maxlength' => true]) ?>
                                </div>
                                <div class="col-md-6">
                                    <?= $form->field($createCategoryModel, 'slug')->textInput(['maxlength' => true])
                                        ->hint('Leave empty to auto-generate from name') ?>
                                </div>
                            </div>
                            
                            <?= $form->field($createCategoryModel, 'description')->textarea(['rows' => 3]) ?>
                            
                            <?= $form->field($createCategoryModel, 'image')->textInput(['maxlength' => true])
                                ->hint('Enter image URL (e.g., https://example.com/image.jpg)') ?>
                            
                            <div class="form-group">
                                <?= Html::submitButton('Create Category', ['class' => 'btn btn-success']) ?>
                                <?= Html::button('Cancel', ['class' => 'btn btn-secondary', 'onclick' => 'toggleCategoryForm()']) ?>
                            </div>
                            
                            <?php ActiveForm::end(); ?>
                        </div>
                    </div>
                    
                    <?php
                    $categories = \app\models\Category::find()->orderBy(['name' => SORT_ASC])->all();
                    ?>
                    
                    <?php if (empty($categories)): ?>
                        <div class="card">
                            <div class="card-body text-center">
                                <p class="text-muted mb-0">No categories yet. Create your first category!</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th style="width: 80px;">Image</th>
                                        <th>Name</th>
                                        <th>Slug</th>
                                        <th>Description</th>
                                        <th>Articles</th>
                                        <th style="width: 200px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories as $category): ?>
                                        <tr id="category-row-<?= $category->id ?>">
                                            <td>
                                                <?php if ($category->image): ?>
                                                    <?= Html::img($category->image, [
                                                        'style' => 'max-width: 50px; max-height: 50px; object-fit: cover;',
                                                        'class' => 'img-thumbnail'
                                                    ]) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td id="category-name-<?= $category->id ?>"><?= Html::encode($category->name) ?></td>
                                            <td id="category-slug-<?= $category->id ?>"><code><?= Html::encode($category->slug) ?></code></td>
                                            <td id="category-desc-<?= $category->id ?>">
                                                <?php if ($category->description): ?>
                                                    <?= \yii\helpers\StringHelper::truncate(Html::encode($category->description), 50) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?= $category->getArticlesCount() ?></span>
                                            </td>
                                            <td>
                                                <div id="category-actions-<?= $category->id ?>" class="d-flex gap-2">
                                                    <button type="button" class="btn btn-sm btn-warning" 
                                                            onclick="showEditForm(<?= $category->id ?>)"
                                                            title="Edit Category">
                                                        <i class="bi bi-pencil"></i> Edit
                                                    </button>
                                                    <?= Html::a('<i class="bi bi-trash"></i> Delete', ['user/category-delete', 'id' => $category->id], [
                                                        'class' => 'btn btn-sm btn-danger',
                                                        'title' => 'Delete Category',
                                                        'data' => [
                                                            'confirm' => 'Are you sure you want to delete this category?',
                                                            'method' => 'post',
                                                        ],
                                                    ]) ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <!-- Форма редактирования (скрыта, под строкой) -->
                                        <tr id="category-edit-form-row-<?= $category->id ?>" style="display: none;">
                                            <td colspan="6">
                                                <div class="card mt-2 mb-2">
                                                    <div class="card-header">
                                                        <h6 class="mb-0">Edit Category: <?= Html::encode($category->name) ?></h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <?php
                                                        $editCategory = \app\models\Category::findOne($category->id);
                                                        $editForm = ActiveForm::begin([
                                                            'action' => ['user/category-update', 'id' => $category->id],
                                                            'method' => 'post',
                                                            'options' => ['class' => 'category-edit-form']
                                                        ]);
                                                        ?>
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <?= $editForm->field($editCategory, 'name')->textInput(['maxlength' => true]) ?>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <?= $editForm->field($editCategory, 'slug')->textInput(['maxlength' => true])
                                                                    ->hint('Leave empty to auto-generate from name') ?>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <?= $editForm->field($editCategory, 'description')->textarea(['rows' => 3]) ?>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <?= $editForm->field($editCategory, 'image')->textInput(['maxlength' => true])
                                                                    ->hint('Enter image URL (e.g., https://example.com/image.jpg)') ?>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <?= Html::submitButton('Save Changes', ['class' => 'btn btn-success']) ?>
                                                            <?= Html::button('Cancel', ['class' => 'btn btn-secondary', 'onclick' => 'hideEditForm(' . $category->id . ')']) ?>
                                                        </div>
                                                        <?php ActiveForm::end(); ?>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
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

    // Перевіряємо, чи є якір в URL (для автоматичного відкриття секції після редиректу)
    const hash = window.location.hash;
    if (hash) {
        const sectionName = hash.replace('#', '').replace('-section', '');
        const targetItem = document.querySelector('[data-section="' + sectionName + '"]');
        if (targetItem) {
            setTimeout(function() {
                targetItem.click();
            }, 100);
        }
    }

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

// Показ/приховування форми створення категорії
function toggleCategoryForm() {
    const form = document.getElementById('category-create-form');
    if (form.style.display === 'none') {
        form.style.display = 'block';
    } else {
        form.style.display = 'none';
    }
}

// Показ форми редагування категорії
function showEditForm(categoryId) {
    // Приховуємо всі інші форми редагування
    document.querySelectorAll('[id^="category-edit-form-row-"]').forEach(function(form) {
        form.style.display = 'none';
    });
    
    // Показуємо дії для всіх категорій
    document.querySelectorAll('[id^="category-actions-"]').forEach(function(actions) {
        actions.style.display = 'block';
    });
    
    // Приховуємо дії для поточної категорії
    document.getElementById('category-actions-' + categoryId).style.display = 'none';
    
    // Показуємо форму редагування (рядок після поточного)
    document.getElementById('category-edit-form-row-' + categoryId).style.display = 'table-row';
}

// Приховування форми редагування категорії
function hideEditForm(categoryId) {
    document.getElementById('category-edit-form-row-' + categoryId).style.display = 'none';
    document.getElementById('category-actions-' + categoryId).style.display = 'block';
}
</script>
