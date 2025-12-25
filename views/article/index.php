<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var app\models\Category[] $categories */
/** @var int|null $selectedCategoryId */
/** @var int|null $selectedTagId */
/** @var string|null $search */
/** @var string $sort */

use yii\helpers\Html;
use yii\widgets\LinkPager;
use yii\helpers\Url;
use app\models\Article;

$this->title = 'Articles';
?>

<div class="articles-page">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar з категоріями -->
            <aside class="col-lg-3 col-md-4 articles-sidebar">
                <div class="sidebar-sticky">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-folder"></i> Categories
                            </h5>
                        </div>
                        <div class="list-group list-group-flush">
                            <?= Html::a(
                                '<i class="bi bi-grid"></i> All Categories',
                                ['index'],
                                [
                                    'class' => 'list-group-item list-group-item-action' . (!$selectedCategoryId ? ' active' : '')
                                ]
                            ) ?>
                            <?php foreach ($categories as $category): ?>
                                <?= Html::a(
                                    '<span class="category-name">' . Html::encode($category->name) . '</span> <span class="badge bg-secondary float-end">' . $category->getArticlesCount() . '</span>',
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
                            <div class="card-body text-center">
                                <?= Html::a(
                                    '<i class="bi bi-plus-circle"></i> Create New Article',
                                    ['create'],
                                    ['class' => 'btn btn-success w-100']
                                ) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </aside>

            <!-- Основна секція  -->
            <main class="col-lg-9 col-md-8 articles-main">
                <div class="articles-header mb-4">
                    <h1 class="page-title"><?= Html::encode($this->title) ?></h1>
                    
                    <!-- Пошук та сортування -->
                    <div class="articles-controls">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <?php $form = \yii\bootstrap5\ActiveForm::begin([
                                    'method' => 'get',
                                    'action' => ['article/index'],
                                    'options' => ['class' => 'search-form']
                                ]); ?>
                                
                                <?php
                                // Зберігаємо поточні параметри фільтрації
                                $currentParams = Yii::$app->request->get();
                                foreach (['category_id', 'tag_id', 'sort'] as $param) {
                                    if (isset($currentParams[$param])) {
                                        echo Html::hiddenInput($param, $currentParams[$param]);
                                    }
                                }
                                ?>
                                
                                <div class="input-group">
                                    <?= Html::textInput('search', $search, [
                                        'class' => 'form-control',
                                        'placeholder' => 'Search articles...',
                                        'aria-label' => 'Search'
                                    ]) ?>
                                    <?= Html::submitButton('<i class="bi bi-search"></i>', [
                                        'class' => 'btn btn-primary',
                                        'title' => 'Search'
                                    ]) ?>
                                    <?php if ($search): ?>
                                        <?= Html::a(
                                            '<i class="bi bi-x"></i>',
                                            array_merge(['article/index'], array_filter([
                                                'category_id' => $selectedCategoryId,
                                                'tag_id' => $selectedTagId,
                                                'sort' => $sort
                                            ])),
                                            [
                                                'class' => 'btn btn-outline-secondary',
                                                'title' => 'Clear search'
                                            ]
                                        ) ?>
                                    <?php endif; ?>
                                </div>
                                <?php \yii\bootstrap5\ActiveForm::end(); ?>
                            </div>
                            
                            <div class="col-md-4">
                                <?php
                                $sortOptions = [
                                    'newest' => 'Newest First',
                                    'oldest' => 'Oldest First',
                                    'popular' => 'Most Popular',
                                    'title_asc' => 'Title A-Z',
                                    'title_desc' => 'Title Z-A',
                                ];
                                
                                $sortUrl = Url::current(['sort' => null]);
                                ?>
                                <div class="sort-dropdown">
                                    <label for="sort-select" class="form-label visually-hidden">Sort by</label>
                                    <select id="sort-select" class="form-select" onchange="window.location.href = '<?= $sortUrl ?>' + (this.value ? '&sort=' + this.value : '')">
                                        <?php foreach ($sortOptions as $value => $label): ?>
                                            <option value="<?= $value ?>" <?= $sort === $value ? 'selected' : '' ?>>
                                                <?= Html::encode($label) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Список статей -->
                <?php if ($dataProvider->getTotalCount() > 0): ?>
                    <div class="articles-list">
                        <?php foreach ($dataProvider->getModels() as $article): ?>
                            <article class="article-card">
                                <?= Html::a(
                                    '<div class="article-card-content">
                                        <div class="article-card-image">
                                            ' . ($article->image 
                                                ? Html::img($article->image, [
                                                    'alt' => Html::encode($article->title),
                                                    'class' => 'img-fluid'
                                                ])
                                                : '<div class="article-placeholder">
                                                    <i class="bi bi-image"></i>
                                                </div>'
                                            ) . '
                                        </div>
                                        <div class="article-card-info">
                                            <div class="article-card-meta">
                                                <span class="article-category">
                                                    <i class="bi bi-folder"></i> ' . Html::encode($article->category->name ?? 'Uncategorized') . '
                                                </span>
                                                <span class="article-date">
                                                    <i class="bi bi-calendar"></i> ' . date('M j, Y', $article->created_at) . '
                                                </span>
                                                <span class="article-views">
                                                    <i class="bi bi-eye"></i> ' . $article->views . '
                                                </span>
                                            </div>
                                            <h2 class="article-card-title">' . Html::encode($article->title) . '</h2>
                                            <p class="article-card-excerpt">' . Html::encode($article->getExcerpt(150)) . '</p>
                                            <div class="article-card-tags">
                                                ' . (empty($article->tags) ? '' : implode('', array_map(function($tag) use ($selectedCategoryId, $search, $sort) {
                                                    return Html::a(
                                                        '#' . Html::encode($tag->name),
                                                        ['index', 'tag_id' => $tag->id],
                                                        [
                                                            'class' => 'tag-badge',
                                                            'onclick' => 'event.stopPropagation(); return true;'
                                                        ]
                                                    );
                                                }, $article->tags))) . '
                                            </div>
                                            <div class="article-card-footer">
                                                <span class="article-author">
                                                    <i class="bi bi-person"></i> ' . Html::encode($article->author->username ?? 'Unknown') . '
                                                </span>
                                                <span class="article-read-more">
                                                    Read more <i class="bi bi-arrow-right"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>',
                                    ['view', 'slug' => $article->slug],
                                    ['class' => 'article-card-link']
                                ) ?>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <!-- Пагінація -->
                    <div class="articles-pagination">
                        <?= LinkPager::widget([
                            'pagination' => $dataProvider->pagination,
                            'options' => ['class' => 'pagination justify-content-center'],
                            'linkOptions' => ['class' => 'page-link'],
                            'activePageCssClass' => 'active',
                            'disabledPageCssClass' => 'disabled',
                            'prevPageLabel' => '<i class="bi bi-chevron-left"></i>',
                            'nextPageLabel' => '<i class="bi bi-chevron-right"></i>',
                        ]) ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <h4><i class="bi bi-info-circle"></i> No articles found</h4>
                        <p>There are no published articles matching your criteria.</p>
                        <?php if (!Yii::$app->user->isGuest && Yii::$app->user->identity->isAuthor()): ?>
                            <?= Html::a(
                                '<i class="bi bi-plus-circle"></i> Create First Article',
                                ['create'],
                                ['class' => 'btn btn-primary']
                            ) ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
</div>
