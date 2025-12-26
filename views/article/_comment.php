<?php

/** @var yii\web\View $this */
/** @var app\models\Comment $comment */
/** @var int $level */

use yii\helpers\Html;
use app\models\Comment;

$marginLeft = $level * 30; // Відступ для вкладених коментарів
?>

<div class="comment mb-3" style="margin-left: <?= $marginLeft ?>px;">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="comment-header">
                    <div class="comment-author-info">
                        <?php if ($comment->user): ?>
                            <?= Html::img(
                                $comment->user->getAvatarUrl(),
                                [
                                    'alt' => Html::encode($comment->user->username),
                                    'class' => 'comment-avatar',
                                ]
                            ) ?>
                        <?php endif; ?>
                        <div class="comment-author-details">
                            <strong><?= Html::encode($comment->getAuthorName()) ?></strong>
                            <small class="text-muted ms-2">
                                <?= date('F j, Y, g:i a', $comment->created_at) ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            
            <p class="mb-2"><?= nl2br(Html::encode($comment->content)) ?></p>
            
            <?php if (!Yii::$app->user->isGuest && $level < 3): ?>
                <button class="btn btn-sm btn-outline-secondary reply-btn" 
                        data-comment-id="<?= $comment->id ?>"
                        data-article-id="<?= $comment->article_id ?>">
                    Reply
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Форма відповіді (скрита за замовчуванням) -->
    <?php if (!Yii::$app->user->isGuest && $level < 3): ?>
        <div class="reply-form mt-2" id="reply-form-<?= $comment->id ?>" style="display: none; margin-left: 20px;">
            <div class="card">
                <div class="card-body">
                    <?php
                    $replyModel = new Comment();
                    $replyModel->article_id = $comment->article_id;
                    $replyModel->parent_id = $comment->id;
                    $replyModel->user_id = Yii::$app->user->id;
                    ?>
                    <?php $form = \yii\bootstrap5\ActiveForm::begin([
                        'action' => ['comment/create'],
                        'method' => 'post',
                    ]); ?>
                    
                    <?= Html::hiddenInput('Comment[article_id]', $comment->article_id) ?>
                    <?= Html::hiddenInput('Comment[parent_id]', $comment->id) ?>
                    <?= Html::hiddenInput('Comment[user_id]', Yii::$app->user->id) ?>
                    
                    <?= $form->field($replyModel, 'content')->textarea(['rows' => 3, 'placeholder' => 'Write your reply...'])->label(false) ?>
                    
                    <div>
                        <?= Html::submitButton('Post Reply', ['class' => 'btn btn-primary btn-sm']) ?>
                        <button type="button" class="btn btn-secondary btn-sm cancel-reply" data-comment-id="<?= $comment->id ?>">Cancel</button>
                    </div>
                    
                    <?php \yii\bootstrap5\ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Вкладені комментарі (відповіді) -->
    <?php
    $replies = $comment->getReplies()->all();
    if (!empty($replies)):
        foreach ($replies as $reply):
            echo $this->render('_comment', ['comment' => $reply, 'level' => $level + 1]);
        endforeach;
    endif;
    ?>
</div>

<script>
// Код для показу/скриття форми відповіді
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.reply-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var commentId = this.getAttribute('data-comment-id');
            var form = document.getElementById('reply-form-' + commentId);
            if (form) {
                form.style.display = form.style.display === 'none' ? 'block' : 'none';
            }
        });
    });

    document.querySelectorAll('.cancel-reply').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var commentId = this.getAttribute('data-comment-id');
            var form = document.getElementById('reply-form-' + commentId);
            if (form) {
                form.style.display = 'none';
                form.querySelector('textarea').value = '';
            }
        });
    });
});
</script>

