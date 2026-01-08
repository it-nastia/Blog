<?php

/** @var yii\web\View $this */
/** @var app\models\User $user */
/** @var app\models\ProfileForm $model */
/** @var array $stats */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

$this->title = 'Profile: ' . Html::encode($user->username);
$this->params['breadcrumbs'][] = ['label' => 'Profile', 'url' => ['profile', 'id' => $user->id]];
?>

<div class="user-profile">
    <?php if ($user->isAuthor()): ?>
        <!-- Layout для автора з боковою панеллю -->
        <div class="row">
            <!-- Бокова панель -->
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

            <!-- Основний контент -->
            <div class="col-lg-9">
                <!-- Секція Профіль -->
                <div id="profile-section" class="profile-section">
                    <h2>Profile</h2>
                    <?= $this->render('_profile_form', ['user' => $user, 'model' => $model]) ?>
                </div>

                <!-- Секція Статті -->
                <div id="articles-section" class="profile-section" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2>Articles</h2>
                        <div class="d-flex gap-2">
                            <?= Html::a('<i class="bi bi-list"></i> View Full List', ['article/manage'], [
                                'class' => 'btn btn-outline-primary',
                                'title' => 'View all articles in separate page'
                            ]) ?>
                            <button type="button" class="btn btn-success" onclick="toggleArticleForm()">
                                <i class="bi bi-plus-circle"></i> Create Article
                            </button>
                        </div>
                    </div>

                    <!-- Форма створення статті -->
                    <div id="article-create-form" class="card mb-4" style="display: none;">
                        <div class="card-header">
                            <h5 class="mb-0">Create New Article</h5>
                        </div>
                        <div class="card-body">
                            <?php
                            $createArticleModel = new \app\models\Article();
                            $createArticleModel->author_id = $user->id;
                            $createArticleModel->status = \app\models\Article::STATUS_DRAFT;
                            $form = ActiveForm::begin([
                                'action' => ['article/create'],
                                'method' => 'post',
                                'id' => 'article-create-form-element',
                                'options' => ['enctype' => 'multipart/form-data']
                            ]);
                            
                            echo Html::hiddenInput('returnUrl', Url::to(['user/profile', 'id' => $user->id, '#' => 'articles-section']));
                            
                            $categories = ArrayHelper::map(\app\models\Category::find()->all(), 'id', 'name');
                            $tags = \app\models\Tag::find()->orderBy(['name' => SORT_ASC])->all();
                            ?>
                            
                            <div class="row">
                                <div class="col-lg-8">
                                    <?= $form->field($createArticleModel, 'title')->textInput(['maxlength' => true, 'placeholder' => 'Enter article title']) ?>
                                    
                                    <?= $form->field($createArticleModel, 'slug')->textInput(['maxlength' => true])
                                        ->hint('Leave empty to auto-generate from title') ?>
                                    
                                    <?= $form->field($createArticleModel, 'content')->textarea(['rows' => 12, 'placeholder' => 'Write your article content here...']) ?>
                                    
                                    <?= $form->field($createArticleModel, 'image')->textInput(['maxlength' => true])
                                        ->hint('Enter image URL (e.g., https://example.com/image.jpg)') ?>
                                </div>
                                
                                <div class="col-lg-4">
                                    <?= $form->field($createArticleModel, 'category_id')->dropDownList(
                                        $categories,
                                        ['prompt' => 'Select category...']
                                    ) ?>
                                    
                                    <?= $form->field($createArticleModel, 'status')->dropDownList([
                                        \app\models\Article::STATUS_DRAFT => 'Draft',
                                        \app\models\Article::STATUS_PUBLISHED => 'Published',
                                    ]) ?>
                                    
                                    <div class="form-group">
                                        <label class="form-label">Tags</label>
                                        <div class="border rounded p-3" style="max-height: 250px; overflow-y: auto; background-color: var(--bg-tertiary);">
                                            <?php foreach ($tags as $tag): ?>
                                                <div class="form-check">
                                                    <?= Html::checkbox('Article[tagIds][]', false, [
                                                        'value' => $tag->id,
                                                        'id' => 'create-tag-' . $tag->id,
                                                        'class' => 'form-check-input'
                                                    ]) ?>
                                                    <?= Html::label($tag->name, 'create-tag-' . $tag->id, ['class' => 'form-check-label']) ?>
                                                </div>
                                            <?php endforeach; ?>
                                            <?php if (empty($tags)): ?>
                                                <p class="text-muted small mb-0">No tags available. Create tags first.</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group mt-3">
                                <?= Html::submitButton('Create Article', ['class' => 'btn btn-success']) ?>
                                <?= Html::button('Cancel', ['class' => 'btn btn-secondary', 'onclick' => 'toggleArticleForm()']) ?>
                            </div>
                            
                            <?php ActiveForm::end(); ?>
                        </div>
                    </div>
                    
                    <?php
                    $articles = \app\models\Article::find()
                        ->where(['author_id' => $user->id])
                        ->orderBy(['created_at' => SORT_DESC])
                        ->all();
                    ?>
                    
                    <?php if (empty($articles)): ?>
                        <div class="card">
                            <div class="card-body text-center">
                                <p class="text-muted mb-0">No articles yet. Create your first article!</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th style="width: 80px;">Image</th>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Status</th>
                                        <th>Views</th>
                                        <th>Created</th>
                                        <th style="width: 200px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($articles as $article): ?>
                                        <tr id="article-row-<?= $article->id ?>">
                                            <td>
                                                <?php if ($article->image): ?>
                                                    <?= Html::img($article->image, [
                                                        'style' => 'max-width: 50px; max-height: 50px; object-fit: cover;',
                                                        'class' => 'img-thumbnail'
                                                    ]) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td id="article-title-<?= $article->id ?>">
                                                <strong><?= Html::encode($article->title) ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    <?php if (!empty($article->tags)): ?>
                                                        <?php foreach ($article->tags as $tag): ?>
                                                            <span class="badge bg-secondary">#<?= Html::encode($tag->name) ?></span>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?= Html::encode($article->category->name ?? '-') ?>
                                            </td>
                                            <td>
                                                <?php if ($article->status === \app\models\Article::STATUS_PUBLISHED): ?>
                                                    <span class="badge bg-success">Published</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">Draft</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?= $article->views ?></span>
                                            </td>
                                            <td style="white-space: nowrap;">
                                                <small><?= date('Y-m-d', $article->created_at) ?></small>
                                            </td>
                                            <td>
                                                <div id="article-actions-<?= $article->id ?>" class="d-flex gap-1">
                                                    <?= Html::a('<i class="bi bi-eye"></i> View', ['/article/view', 'slug' => $article->slug], [
                                                        'class' => 'btn btn-sm btn-outline-primary',
                                                        'title' => 'View Article',
                                                        'target' => '_blank'
                                                    ]) ?>
                                                    <button type="button" class="btn btn-sm btn-warning" 
                                                            onclick="showArticleEditForm(<?= $article->id ?>)"
                                                            title="Edit Article">
                                                        <i class="bi bi-pencil"></i> Edit
                                                    </button>
                                                    <?= Html::a('<i class="bi bi-trash"></i> Delete', ['article/delete', 'id' => $article->id, 'returnUrl' => Url::to(['user/profile', 'id' => $user->id, '#' => 'articles-section'])], [
                                                        'class' => 'btn btn-sm btn-danger',
                                                        'title' => 'Delete Article',
                                                        'data' => [
                                                            'confirm' => 'Are you sure you want to delete this article?',
                                                            'method' => 'post',
                                                        ],
                                                    ]) ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <!-- Форма редагування (скрита, під рядком) -->
                                        <tr id="article-edit-form-row-<?= $article->id ?>" style="display: none;">
                                            <td colspan="7">
                                                <div class="card mt-2 mb-2">
                                                    <div class="card-header">
                                                        <h6 class="mb-0">Edit Article: <?= Html::encode($article->title) ?></h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <?php
                                                        $editArticle = \app\models\Article::findOne($article->id);
                                                        $editForm = ActiveForm::begin([
                                                            'action' => ['article/update', 'id' => $article->id],
                                                            'method' => 'post',
                                                            'options' => ['class' => 'article-edit-form', 'enctype' => 'multipart/form-data']
                                                        ]);
                                                        
                                                        // Додаємо hidden поле для returnUrl
                                                        echo Html::hiddenInput('returnUrl', Url::to(['user/profile', 'id' => $user->id, '#' => 'articles-section']));
                                                        
                                                        $editCategories = ArrayHelper::map(\app\models\Category::find()->all(), 'id', 'name');
                                                        $editTags = \app\models\Tag::find()->orderBy(['name' => SORT_ASC])->all();
                                                        $selectedTagIds = ArrayHelper::getColumn($editArticle->tags, 'id');
                                                        ?>
                                                        <div class="row">
                                                            <div class="col-lg-8">
                                                                <?= $editForm->field($editArticle, 'title')->textInput(['maxlength' => true]) ?>
                                                                
                                                                <?= $editForm->field($editArticle, 'slug')->textInput(['maxlength' => true])
                                                                    ->hint('Leave empty to auto-generate from title') ?>
                                                                
                                                                <?= $editForm->field($editArticle, 'content')->textarea(['rows' => 12]) ?>
                                                                
                                                                <?= $editForm->field($editArticle, 'image')->textInput(['maxlength' => true])
                                                                    ->hint('Enter image URL (e.g., https://example.com/image.jpg)') ?>
                                                            </div>
                                                            
                                                            <div class="col-lg-4">
                                                                <?= $editForm->field($editArticle, 'category_id')->dropDownList(
                                                                    $editCategories,
                                                                    ['prompt' => 'Select category...']
                                                                ) ?>
                                                                
                                                                <?= $editForm->field($editArticle, 'status')->dropDownList([
                                                                    \app\models\Article::STATUS_DRAFT => 'Draft',
                                                                    \app\models\Article::STATUS_PUBLISHED => 'Published',
                                                                ]) ?>
                                                                
                                                                <div class="form-group">
                                                                    <label class="form-label">Tags</label>
                                                                    <div class="border rounded p-3" style="max-height: 250px; overflow-y: auto; background-color: var(--bg-tertiary);">
                                                                        <?php foreach ($editTags as $tag): ?>
                                                                            <div class="form-check">
                                                                                <?= Html::checkbox('Article[tagIds][]', in_array($tag->id, $selectedTagIds), [
                                                                                    'value' => $tag->id,
                                                                                    'id' => 'edit-tag-' . $article->id . '-' . $tag->id,
                                                                                    'class' => 'form-check-input'
                                                                                ]) ?>
                                                                                <?= Html::label($tag->name, 'edit-tag-' . $article->id . '-' . $tag->id, ['class' => 'form-check-label']) ?>
                                                                            </div>
                                                                        <?php endforeach; ?>
                                                                        <?php if (empty($editTags)): ?>
                                                                            <p class="text-muted small mb-0">No tags available. Create tags first.</p>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group mt-3">
                                                            <?= Html::submitButton('Save Changes', ['class' => 'btn btn-success']) ?>
                                                            <?= Html::button('Cancel', ['class' => 'btn btn-secondary', 'onclick' => 'hideArticleEditForm(' . $article->id . ')']) ?>
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

                <!-- Секція Категорії -->
                <div id="categories-section" class="profile-section" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2>Categories</h2>
                        <div class="d-flex gap-2">
                            <?= Html::a('<i class="bi bi-list"></i> View Full List', ['category/index'], [
                                'class' => 'btn btn-outline-primary',
                                'title' => 'View all categories in separate page'
                            ]) ?>
                            <button type="button" class="btn btn-success" onclick="toggleCategoryForm()">
                                <i class="bi bi-plus-circle"></i> Create Category
                            </button>
                        </div>
                    </div>

                    <!-- Форма створення категорії -->
                    <div id="category-create-form" class="card mb-4" style="display: none;">
                        <div class="card-header">
                            <h5 class="mb-0">Create New Category</h5>
                        </div>
                        <div class="card-body">
                            <?php
                            $createCategoryModel = new \app\models\Category();
                            $form = ActiveForm::begin([
                                'action' => ['category/create'],
                                'method' => 'post',
                                'id' => 'category-create-form-element'
                            ]);
                            
                            // Додаємо hidden поле для returnUrl
                            echo Html::hiddenInput('returnUrl', Url::to(['user/profile', 'id' => $user->id, '#' => 'categories-section']));
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
                                                <div id="category-actions-<?= $category->id ?>" class="d-flex gap-1">
                                                    <button type="button" class="btn btn-sm btn-warning" 
                                                            onclick="showEditForm(<?= $category->id ?>)"
                                                            title="Edit Category">
                                                        <i class="bi bi-pencil"></i> Edit
                                                    </button>
                                                    <?= Html::a('<i class="bi bi-trash"></i> Delete', ['category/delete', 'id' => $category->id, 'returnUrl' => Url::to(['user/profile', 'id' => $user->id, '#' => 'categories-section'])], [
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
                                                            'action' => ['category/update', 'id' => $category->id],
                                                            'method' => 'post',
                                                            'options' => ['class' => 'category-edit-form']
                                                        ]);
                                                        
                                                        // Додаємо hidden поле для returnUrl
                                                        echo Html::hiddenInput('returnUrl', Url::to(['user/profile', 'id' => $user->id, '#' => 'categories-section']));
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

                <!-- Секція Теги -->
                <div id="tags-section" class="profile-section" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2>Tags</h2>
                        <div class="d-flex gap-2">
                            <?= Html::a('<i class="bi bi-list"></i> View Full List', ['tag/index'], [
                                'class' => 'btn btn-outline-primary',
                                'title' => 'View all tags in separate page'
                            ]) ?>
                            <button type="button" class="btn btn-success" onclick="toggleTagForm()">
                                <i class="bi bi-plus-circle"></i> Create Tag
                            </button>
                        </div>
                    </div>

                    <!-- Форма створення тега -->
                    <div id="tag-create-form" class="card mb-4" style="display: none;">
                        <div class="card-header">
                            <h5 class="mb-0">Create New Tag</h5>
                        </div>
                        <div class="card-body">
                            <?php
                            $createTagModel = new \app\models\Tag();
                            $form = ActiveForm::begin([
                                'action' => ['tag/create'],
                                'method' => 'post',
                                'id' => 'tag-create-form-element'
                            ]);
                            
                            // Додаємо hidden поле для returnUrl
                            echo Html::hiddenInput('returnUrl', Url::to(['user/profile', 'id' => $user->id, '#' => 'tags-section']));
                            ?>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <?= $form->field($createTagModel, 'name')->textInput(['maxlength' => true]) ?>
                                </div>
                                <div class="col-md-6">
                                    <?= $form->field($createTagModel, 'slug')->textInput(['maxlength' => true])
                                        ->hint('Leave empty to auto-generate from name') ?>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <?= Html::submitButton('Create Tag', ['class' => 'btn btn-success']) ?>
                                <?= Html::button('Cancel', ['class' => 'btn btn-secondary', 'onclick' => 'toggleTagForm()']) ?>
                            </div>
                            
                            <?php ActiveForm::end(); ?>
                        </div>
                    </div>
                    
                    <?php
                    $tags = \app\models\Tag::find()->orderBy(['name' => SORT_ASC])->all();
                    ?>
                    
                    <?php if (empty($tags)): ?>
                        <div class="card">
                            <div class="card-body text-center">
                                <p class="text-muted mb-0">No tags yet. Create your first tag!</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Slug</th>
                                        <th>Articles</th>
                                        <th>Created At</th>
                                        <th style="width: 200px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tags as $tag): ?>
                                        <tr id="tag-row-<?= $tag->id ?>">
                                            <td id="tag-name-<?= $tag->id ?>"><?= Html::encode($tag->name) ?></td>
                                            <td id="tag-slug-<?= $tag->id ?>"><code><?= Html::encode($tag->slug) ?></code></td>
                                            <td>
                                                <span class="badge bg-info"><?= $tag->getArticlesCount() ?></span>
                                            </td>
                                            <td style="white-space: nowrap;">
                                                <?= date('Y-m-d H:i', $tag->created_at) ?>
                                            </td>
                                            <td>
                                                <div id="tag-actions-<?= $tag->id ?>" class="d-flex gap-1">
                                                    <button type="button" class="btn btn-sm btn-warning" 
                                                            onclick="showTagEditForm(<?= $tag->id ?>)"
                                                            title="Edit Tag">
                                                        <i class="bi bi-pencil"></i> Edit
                                                    </button>
                                                    <?= Html::a('<i class="bi bi-trash"></i> Delete', ['tag/delete', 'id' => $tag->id, 'returnUrl' => Url::to(['user/profile', 'id' => $user->id, '#' => 'tags-section'])], [
                                                        'class' => 'btn btn-sm btn-danger',
                                                        'title' => 'Delete Tag',
                                                        'data' => [
                                                            'confirm' => 'Are you sure you want to delete this tag?',
                                                            'method' => 'post',
                                                        ],
                                                    ]) ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <!-- Форма редагування (скрита, під рядком) -->
                                        <tr id="tag-edit-form-row-<?= $tag->id ?>" style="display: none;">
                                            <td colspan="5">
                                                <div class="card mt-2 mb-2">
                                                    <div class="card-header">
                                                        <h6 class="mb-0">Edit Tag: <?= Html::encode($tag->name) ?></h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <?php
                                                        $editTag = \app\models\Tag::findOne($tag->id);
                                                        $editForm = ActiveForm::begin([
                                                            'action' => ['tag/update', 'id' => $tag->id],
                                                            'method' => 'post',
                                                            'options' => ['class' => 'tag-edit-form']
                                                        ]);
                                                        
                                                        // Додаємо hidden поле для returnUrl
                                                        echo Html::hiddenInput('returnUrl', Url::to(['user/profile', 'id' => $user->id, '#' => 'tags-section']));
                                                        ?>
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <?= $editForm->field($editTag, 'name')->textInput(['maxlength' => true]) ?>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <?= $editForm->field($editTag, 'slug')->textInput(['maxlength' => true])
                                                                    ->hint('Leave empty to auto-generate from name') ?>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <?= Html::submitButton('Save Changes', ['class' => 'btn btn-success']) ?>
                                                            <?= Html::button('Cancel', ['class' => 'btn btn-secondary', 'onclick' => 'hideTagEditForm(' . $tag->id . ')']) ?>
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

                <!-- Секція Коментарі -->
                <div id="comments-section" class="profile-section" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2>Comments</h2>
                        <?= Html::a('<i class="bi bi-list"></i> View Full List', ['comment/index'], [
                            'class' => 'btn btn-outline-primary',
                            'title' => 'View all comments in separate page'
                        ]) ?>
                    </div>
                    
                    <?php
                    // Отримуємо всі коментарі до статтей автора
                    $comments = \app\models\Comment::find()
                        ->joinWith('article')
                        ->where(['articles.author_id' => $user->id])
                        ->orderBy(['comments.created_at' => SORT_DESC])
                        ->all();
                    ?>
                    
                    <?php if (empty($comments)): ?>
                        <div class="card">
                            <div class="card-body text-center">
                                <p class="text-muted mb-0">No comments yet.</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;">ID</th>
                                        <th>Article</th>
                                        <th>Author</th>
                                        <th>Content</th>
                                        <th>Status</th>
                                        <th style="white-space: nowrap;">Created</th>
                                        <th style="width: 150px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($comments as $comment): ?>
                                        <tr id="comment-row-<?= $comment->id ?>">
                                            <td><?= $comment->id ?></td>
                                            <td>
                                                <?= Html::a(
                                                    Html::encode($comment->article->title ?? 'N/A'),
                                                    ['/article/view', 'slug' => $comment->article->slug ?? ''],
                                                    ['target' => '_blank', 'class' => 'text-decoration-none']
                                                ) ?>
                                                <?php if ($comment->parent_id): ?>
                                                    <br><small class="text-muted">Reply to comment #<?= $comment->parent_id ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($comment->user): ?>
                                                    <?= Html::encode($comment->user->username) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">Guest</span>
                                                <?php endif; ?>
                                            </td>
                                            <td id="comment-content-<?= $comment->id ?>">
                                                <?= \yii\helpers\StringHelper::truncate(Html::encode($comment->content), 100) ?>
                                            </td>
                                            <td id="comment-status-<?= $comment->id ?>">
                                                <?php
                                                $editComment = \app\models\Comment::findOne($comment->id);
                                                $statusForm = ActiveForm::begin([
                                                    'action' => ['comment/update', 'id' => $comment->id],
                                                    'method' => 'post',
                                                    'options' => [
                                                        'class' => 'comment-status-form',
                                                        'style' => 'margin: 0;'
                                                    ]
                                                ]);
                                                
                                                // Додаємо hidden поле для returnUrl
                                                echo Html::hiddenInput('returnUrl', Url::to(['user/profile', 'id' => $user->id, '#' => 'comments-section']));
                                                ?>
                                                <?= $statusForm->field($editComment, 'status', [
                                                    'options' => ['class' => 'mb-0']
                                                ])->dropDownList([
                                                    \app\models\Comment::STATUS_PENDING => 'Pending',
                                                    \app\models\Comment::STATUS_APPROVED => 'Approved',
                                                    \app\models\Comment::STATUS_REJECTED => 'Rejected',
                                                ], [
                                                    'class' => 'form-select form-select-sm',
                                                    'onchange' => 'this.form.submit();',
                                                    'style' => 'min-width: 110px;'
                                                ])->label(false) ?>
                                                <?php ActiveForm::end(); ?>
                                            </td>
                                            <td style="white-space: nowrap;">
                                                <small><?= date('Y-m-d H:i', $comment->created_at) ?></small>
                                            </td>
                                            <td>
                                                <div id="comment-actions-<?= $comment->id ?>" class="d-flex gap-1">
                                                    <?= Html::a('<i class="bi bi-trash"></i> Delete', ['comment/delete', 'id' => $comment->id, 'returnUrl' => Url::to(['user/profile', 'id' => $user->id, '#' => 'comments-section'])], [
                                                        'class' => 'btn btn-sm btn-danger',
                                                        'title' => 'Delete Comment',
                                                        'data' => [
                                                            'confirm' => 'Are you sure you want to delete this comment?',
                                                            'method' => 'post',
                                                        ],
                                                    ]) ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Layout для читача (без бокової панелі) -->
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
        actions.style.display = 'flex';
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

// Показ/приховування форми створення тега
function toggleTagForm() {
    const form = document.getElementById('tag-create-form');
    if (form.style.display === 'none') {
        form.style.display = 'block';
    } else {
        form.style.display = 'none';
    }
}

// Показ форми редагування тега
function showTagEditForm(tagId) {
    // Приховуємо всі інші форми редагування
    document.querySelectorAll('[id^="tag-edit-form-row-"]').forEach(function(form) {
        form.style.display = 'none';
    });
    
    // Показуємо дії для всіх тегів
    document.querySelectorAll('[id^="tag-actions-"]').forEach(function(actions) {
        actions.style.display = 'flex';
    });
    
    // Приховуємо дії для поточного тега
    document.getElementById('tag-actions-' + tagId).style.display = 'none';
    
    // Показуємо форму редагування (рядок після поточного)
    document.getElementById('tag-edit-form-row-' + tagId).style.display = 'table-row';
}

// Приховування форми редагування тега
function hideTagEditForm(tagId) {
    document.getElementById('tag-edit-form-row-' + tagId).style.display = 'none';
    document.getElementById('tag-actions-' + tagId).style.display = 'flex';
}

// Показ/приховування форми створення статті
function toggleArticleForm() {
    const form = document.getElementById('article-create-form');
    if (form.style.display === 'none') {
        form.style.display = 'block';
    } else {
        form.style.display = 'none';
    }
}

// Показ форми редагування статті
function showArticleEditForm(articleId) {
    // Приховуємо всі інші форми редагування
    document.querySelectorAll('[id^="article-edit-form-row-"]').forEach(function(form) {
        form.style.display = 'none';
    });
    
    // Показуємо дії для всіх статей
    document.querySelectorAll('[id^="article-actions-"]').forEach(function(actions) {
        actions.style.display = 'flex';
    });
    
    // Приховуємо дії для поточної статті
    document.getElementById('article-actions-' + articleId).style.display = 'none';
    
    // Показуємо форму редагування (рядок після поточного)
    document.getElementById('article-edit-form-row-' + articleId).style.display = 'table-row';
}

// Приховування форми редагування статті
function hideArticleEditForm(articleId) {
    document.getElementById('article-edit-form-row-' + articleId).style.display = 'none';
    document.getElementById('article-actions-' + articleId).style.display = 'flex';
}

</script>
