<?php

/** @var yii\web\View $this */
/** @var app\models\Article $model */

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\Comment;

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Articles', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="article-view">
    <article class="mb-4">
        <!-- Заголовок -->
        <header class="mb-4">
            <h1><?= Html::encode($model->title) ?></h1>
            <p class="text-muted">
                <small>
                    By <?= Html::encode($model->author->username) ?> 
                    | <?= date('F j, Y', $model->created_at) ?>
                    | <?= Html::a(Html::encode($model->category->name), ['index', 'category_id' => $model->category_id], ['class' => 'text-decoration-none']) ?>
                    | <i class="bi bi-eye"></i> <?= $model->views ?> views
                </small>
            </p>
            
            <!-- Теги -->
            <div class="mb-3">
                <?php foreach ($model->tags as $tag): ?>
                    <?= Html::a(
                        '#' . Html::encode($tag->name),
                        ['index', 'tag_id' => $tag->id],
                        ['class' => 'badge bg-secondary text-decoration-none me-1']
                    ) ?>
                <?php endforeach; ?>
            </div>

            <!-- Кнопки дій для автора -->
            <?php if (!Yii::$app->user->isGuest && Yii::$app->user->identity->isAuthor() && $model->author_id == Yii::$app->user->id): ?>
                <div class="mb-3">
                    <?= Html::a('Edit', ['update', 'id' => $model->id], ['class' => 'btn btn-warning btn-sm']) ?>
                    <?= Html::a('Delete', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger btn-sm',
                        'data' => [
                            'confirm' => 'Are you sure you want to delete this article?',
                            'method' => 'post',
                        ],
                    ]) ?>
                </div>
            <?php endif; ?>
        </header>

        <!-- Зображення -->
        <?php if ($model->image): ?>
            <div class="mb-4">
                <img src="<?= Html::encode($model->image) ?>" class="img-fluid rounded" alt="<?= Html::encode($model->title) ?>">
            </div>
        <?php endif; ?>

        <!-- Зміст статті -->
        <div class="article-content">
            <?= nl2br(Html::encode($model->content)) ?>
        </div>

        <!-- Кнопки соціальних мереж -->
        <div class="mt-4 pt-4 border-top">
            <h5>Share this article:</h5>
            <div class="social-share">
                <?php
                $articleUrl = Url::to(['view', 'slug' => $model->slug], true);
                $articleTitle = urlencode($model->title);
                ?>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($articleUrl) ?>" 
                   target="_blank" class="btn btn-outline-primary btn-sm me-2">
                    Facebook
                </a>
                <a href="https://twitter.com/intent/tweet?url=<?= urlencode($articleUrl) ?>&text=<?= $articleTitle ?>" 
                   target="_blank" class="btn btn-outline-info btn-sm me-2">
                    Twitter
                </a>
                <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?= urlencode($articleUrl) ?>&title=<?= $articleTitle ?>" 
                   target="_blank" class="btn btn-outline-primary btn-sm">
                    LinkedIn
                </a>
            </div>
        </div>
    </article>

    <!-- Коментарі -->
    <div class="comments-section mt-5">
        <h3>Comments (<?= $model->getCommentsCount() ?>)</h3>
        
        <?php if (!Yii::$app->user->isGuest): ?>
            <!-- Форма додавання коментаря -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5>Add Comment</h5>
                    <?php
                    $commentModel = new Comment();
                    $commentModel->article_id = $model->id;
                    $commentModel->user_id = Yii::$app->user->id;
                    ?>
                    <?php $form = \yii\bootstrap5\ActiveForm::begin([
                        'action' => ['comment/create'],
                        'method' => 'post',
                    ]); ?>
                    
                    <?= Html::hiddenInput('Comment[article_id]', $model->id) ?>
                    <?= Html::hiddenInput('Comment[user_id]', Yii::$app->user->id) ?>
                    
                    <?= $form->field($commentModel, 'content')->textarea(['rows' => 4, 'placeholder' => 'Write your comment...'])->label(false) ?>
                    
                    <?= Html::submitButton('Post Comment', ['class' => 'btn btn-primary']) ?>
                    
                    <?php \yii\bootstrap5\ActiveForm::end(); ?>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <?= Html::a('Login', ['/site/login']) ?> to post a comment.
            </div>
        <?php endif; ?>

        <!-- Список коментарів -->
        <div class="comments-list">
            <?php
            $rootComments = Comment::findRootComments($model->id)->all();
            if (empty($rootComments)):
            ?>
                <p class="text-muted">No comments yet. Be the first to comment!</p>
            <?php else: ?>
                <?php foreach ($rootComments as $comment): ?>
                    <?= $this->render('_comment', ['comment' => $comment, 'level' => 0]) ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

