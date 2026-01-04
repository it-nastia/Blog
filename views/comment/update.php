<?php

/** @var yii\web\View $this */
/** @var app\models\Comment $model */
/** @var string|null $returnUrl */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Update Comment #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Comments', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Comment #' . $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>

<div class="comment-update">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1><?= Html::encode($this->title) ?></h1>
        <?= Html::a('Back to List', ['index'], ['class' => 'btn btn-secondary']) ?>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="mb-3">
                <strong>Article:</strong>
                <?php if ($model->article): ?>
                    <?= Html::a(
                        Html::encode($model->article->title),
                        ['article/view', 'slug' => $model->article->slug],
                        ['target' => '_blank']
                    ) ?>
                <?php else: ?>
                    <span class="text-muted">Article not found</span>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <strong>User:</strong>
                <?php if ($model->user): ?>
                    <?= Html::encode($model->user->username) ?>
                <?php else: ?>
                    <span class="text-muted">Guest</span>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <strong>Content:</strong>
                <div class="border p-2 bg-light rounded">
                    <?= nl2br(Html::encode($model->content)) ?>
                </div>
            </div>

            <?php $form = ActiveForm::begin(); ?>

            <?= $form->field($model, 'status')->dropDownList([
                \app\models\Comment::STATUS_PENDING => 'Pending',
                \app\models\Comment::STATUS_APPROVED => 'Approved',
                \app\models\Comment::STATUS_REJECTED => 'Rejected',
            ], ['prompt' => 'Select status...']) ?>

            <?php if ($returnUrl): ?>
                <?= Html::hiddenInput('returnUrl', $returnUrl) ?>
            <?php endif; ?>

            <div class="form-group">
                <?= Html::submitButton('Update Status', ['class' => 'btn btn-primary']) ?>
                <?= Html::a('Cancel', $returnUrl ? $returnUrl : ['index'], ['class' => 'btn btn-secondary']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

