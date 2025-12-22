<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var app\models\Category[] $categories */
/** @var int|null $selectedCategoryId */
/** @var int|null $selectedTagId */
/** @var string|null $search */

use yii\helpers\Html;
use yii\widgets\LinkPager;
use yii\helpers\Url;
use app\models\Article;

$this->title = 'Articles';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="article-index">
    <div class="row">
        <div class="col-lg-9">
            <h1><?= Html::encode($this->title) ?></h1>

            <!-- Поиск -->
            <div class="mb-4">
                <?php $form = \yii\bootstrap5\ActiveForm::begin([
                    'method' => 'get',
                    'action' => ['article/index'],
                ]); ?>
                <div class="input-group">
                    <?= Html::textInput('search', $search, [
                        'class' => 'form-control',
                        'placeholder' => 'Search articles...'
                    ]) ?>
                    <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
                    <?php if ($search): ?>
                        <?= Html::a('Clear', ['article/index'], ['class' => 'btn btn-outline-secondary']) ?>
                    <?php endif; ?>
                </div>
                <?php \yii\bootstrap5\ActiveForm::end(); ?>
            </div>

            <!-- Список статей -->
            <?php if ($dataProvider->getTotalCount() > 0): ?>
                <?php foreach ($dataProvider->getModels() as $article): ?>
                    <div class="card mb-4">
                        <?php if ($article->image): ?>
                            <img src="<?= Html::encode($article->image) ?>" class="card-img-top" alt="<?= Html::encode($article->title) ?>" style="max-height: 300px; object-fit: cover;">
                        <?php endif; ?>
                        <div class="card-body">
                            <h2 class="card-title">
                                <?= Html::a(Html::encode($article->title), ['view', 'slug' => $article->slug], ['class' => 'text-decoration-none']) ?>
                            </h2>
                            <p class="text-muted">
                                <small>
                                    By <?= Html::encode($article->author->username) ?> 
                                    | <?= date('F j, Y', $article->created_at) ?>
                                    | <?= Html::a(Html::encode($article->category->name), ['index', 'category_id' => $article->category_id], ['class' => 'text-decoration-none']) ?>
                                    | <i class="bi bi-eye"></i> <?= $article->views ?> views
                                </small>
                            </p>
                            <p class="card-text">
                                <?= Html::encode($article->getExcerpt(200)) ?>
                            </p>
                            <div class="mb-2">
                                <?php foreach ($article->tags as $tag): ?>
                                    <?= Html::a(
                                        '#' . Html::encode($tag->name),
                                        ['index', 'tag_id' => $tag->id],
                                        ['class' => 'badge bg-secondary text-decoration-none me-1']
                                    ) ?>
                                <?php endforeach; ?>
                            </div>
                            <?= Html::a('Read more →', ['view', 'slug' => $article->slug], ['class' => 'btn btn-primary']) ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Пагінація -->
                <div class="mt-4">
                    <?= LinkPager::widget([
                        'pagination' => $dataProvider->pagination,
                        'options' => ['class' => 'pagination justify-content-center'],
                        'linkOptions' => ['class' => 'page-link'],
                        'activePageCssClass' => 'active',
                        'disabledPageCssClass' => 'disabled',
                    ]) ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <h4>No articles found</h4>
                    <p>There are no published articles yet.</p>
                    <?php if (!Yii::$app->user->isGuest && Yii::$app->user->identity->isAuthor()): ?>
                        <?= Html::a('Create First Article', ['create'], ['class' => 'btn btn-primary']) ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Бокова панель -->
        <div class="col-lg-3">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Categories</h5>
                </div>
                <div class="list-group list-group-flush">
                    <?= Html::a('All Categories', ['index'], [
                        'class' => 'list-group-item list-group-item-action' . (!$selectedCategoryId ? ' active' : '')
                    ]) ?>
                    <?php foreach ($categories as $category): ?>
                        <?= Html::a(
                            Html::encode($category->name) . ' <span class="badge bg-secondary">' . $category->getArticlesCount() . '</span>',
                            ['index', 'category_id' => $category->id],
                            [
                                'class' => 'list-group-item list-group-item-action' . ($selectedCategoryId == $category->id ? ' active' : '')
                            ]
                        ) ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if (!Yii::$app->user->isGuest && Yii::$app->user->identity->isAuthor()): ?>
                <div class="card">
                    <div class="card-body">
                        <?= Html::a('Create New Article', ['create'], ['class' => 'btn btn-success w-100']) ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

