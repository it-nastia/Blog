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
    <!-- Основна секція -->
    <div class="article-main-section">
        <div class="article-image-container">
            <?php if ($model->image): ?>
                <img src="<?= Html::encode($model->image) ?>" class="article-main-image" alt="<?= Html::encode($model->title) ?>">
            <?php else: ?>
                <div class="article-image-placeholder">
                    <i class="bi bi-image"></i>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="article-info-container">
            <!-- Назва -->
            <header class="article-header">
                <h1 class="article-title"><?= Html::encode($model->title) ?></h1>
            </header>
            
            <!-- Метаданні-->
            <div class="article-meta">
                <div class="meta-item">
                    <i class="bi bi-person"></i>
                    <span><?= Html::encode($model->author->username) ?></span>
                </div>
                <div class="meta-item">
                    <i class="bi bi-calendar"></i>
                    <span><?= date('F j, Y', $model->created_at) ?></span>
                </div>
                <div class="meta-item">
                    <i class="bi bi-folder"></i>
                    <?= Html::a(Html::encode($model->category->name), ['index', 'category_id' => $model->category_id], ['class' => 'category-link']) ?>
                </div>
                <div class="meta-item">
                    <i class="bi bi-eye"></i>
                    <span><?= $model->views ?> views</span>
                </div>
            </div>

            <!-- Контент  -->
            <div class="article-content"><?= nl2br(Html::encode($model->content)) ?></div>
            
            <!-- Теги -->
            <?php if (!empty($model->tags)): ?>
                <div class="article-tags">
                <?php foreach ($model->tags as $tag): ?>
                    <?= Html::a(
                        '#' . Html::encode($tag->name),
                        ['index', 'tag_id' => $tag->id],
                        ['class' => 'tag-badge']
                    ) ?>
                <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Кнопки дій для автора -->
            <?php if (!Yii::$app->user->isGuest && Yii::$app->user->identity->isAuthor() && $model->author_id == Yii::$app->user->id): ?>
                <div class="article-actions">
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

            <!-- Кнопки соціальних мереж -->
            <div class="article-share-section">
                <h5 class="share-title">Share this article:</h5>
                <div class="social-share">
                    <?php
                    $articleUrl = Url::to(['view', 'slug' => $model->slug], true);
                    $articleTitle = urlencode($model->title);
                    ?>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($articleUrl) ?>" 
                       target="_blank" class="social-btn social-facebook">
                        <i class="bi bi-facebook"></i>
                        <span>Facebook</span>
                    </a>
                    <a href="https://twitter.com/intent/tweet?url=<?= urlencode($articleUrl) ?>&text=<?= $articleTitle ?>" 
                       target="_blank" class="social-btn social-twitter">
                        <i class="bi bi-twitter"></i>
                        <span>Twitter</span>
                    </a>
                    <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?= urlencode($articleUrl) ?>&title=<?= $articleTitle ?>" 
                       target="_blank" class="social-btn social-linkedin">
                        <i class="bi bi-linkedin"></i>
                        <span>LinkedIn</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Коментарі -->
    <div class="comments-section">
        <h3 class="comments-title">Comments (<?= $model->getCommentsCount() ?>)</h3>
        
        <?php if (!Yii::$app->user->isGuest): ?>
            <!-- Форма додавання коментаря -->
            <div class="comment-form-card">
                <h5 class="comment-form-title">Add Comment</h5>
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
                
                <?= $form->field($commentModel, 'content')->textarea(['rows' => 4, 'placeholder' => 'Write your comment...', 'class' => 'form-control comment-textarea'])->label(false) ?>
                
                <?= Html::submitButton('Post Comment', ['class' => 'btn btn-primary']) ?>
                
                <?php \yii\bootstrap5\ActiveForm::end(); ?>
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
                <p class="no-comments">No comments yet. Be the first to comment!</p>
            <?php else: ?>
                <?php foreach ($rootComments as $comment): ?>
                    <?= $this->render('_comment', ['comment' => $comment, 'level' => 0]) ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

