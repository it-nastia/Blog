<?php

/** @var yii\web\View $this */
/** @var app\models\Comment $model */

use yii\bootstrap5\Html;

$this->title = 'Comment #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Comments', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="comment-view">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1><?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::a('Update Status', ['update', 'id' => $model->id], ['class' => 'btn btn-warning']) ?>
            <?= Html::a('Delete', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Are you sure you want to delete this comment?',
                    'method' => 'post',
                ],
            ]) ?>
            <?= Html::a('Back to List', ['index'], ['class' => 'btn btn-secondary']) ?>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-bordered">
                <tr>
                    <th style="width: 200px;">ID</th>
                    <td><?= $model->id ?></td>
                </tr>
                <tr>
                    <th>Article</th>
                    <td>
                        <?php if ($model->article): ?>
                            <?= Html::a(
                                Html::encode($model->article->title),
                                ['article/view', 'slug' => $model->article->slug],
                                ['target' => '_blank']
                            ) ?>
                        <?php else: ?>
                            <span class="text-muted">Article not found</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>User</th>
                    <td>
                        <?php if ($model->user): ?>
                            <?= Html::encode($model->user->username) ?>
                        <?php else: ?>
                            <span class="text-muted">Guest</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>Parent Comment</th>
                    <td>
                        <?php if ($model->parent): ?>
                            <?= Html::a(
                                'View Parent Comment #' . $model->parent->id,
                                ['view', 'id' => $model->parent->id]
                            ) ?>
                        <?php else: ?>
                            <span class="text-muted">Root comment</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>
                        <?php
                        $statusLabels = [
                            'pending' => '<span class="badge bg-warning">Pending</span>',
                            'approved' => '<span class="badge bg-success">Approved</span>',
                            'rejected' => '<span class="badge bg-danger">Rejected</span>',
                        ];
                        echo $statusLabels[$model->status] ?? $model->status;
                        ?>
                    </td>
                </tr>
                <tr>
                    <th>Content</th>
                    <td><?= nl2br(Html::encode($model->content)) ?></td>
                </tr>
                <tr>
                    <th>Created At</th>
                    <td><?= date('Y-m-d H:i:s', $model->created_at) ?></td>
                </tr>
                <tr>
                    <th>Updated At</th>
                    <td><?= date('Y-m-d H:i:s', $model->updated_at) ?></td>
                </tr>
            </table>
        </div>
    </div>

    <?php if (!empty($model->replies)): ?>
        <div class="mt-4">
            <h3>Replies (<?= count($model->replies) ?>)</h3>
            <div class="list-group">
                <?php foreach ($model->replies as $reply): ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>
                                    <?php if ($reply->user): ?>
                                        <?= Html::encode($reply->user->username) ?>
                                    <?php else: ?>
                                        Guest
                                    <?php endif; ?>
                                </strong>
                                <span class="badge bg-secondary ms-2">
                                    <?php
                                    $statusLabels = [
                                        'pending' => 'Pending',
                                        'approved' => 'Approved',
                                        'rejected' => 'Rejected',
                                    ];
                                    echo $statusLabels[$reply->status] ?? $reply->status;
                                    ?>
                                </span>
                                <div class="mt-2"><?= nl2br(Html::encode($reply->content)) ?></div>
                                <small class="text-muted">
                                    <?= date('Y-m-d H:i:s', $reply->created_at) ?>
                                </small>
                            </div>
                            <div>
                                <?= Html::a('View', ['view', 'id' => $reply->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

