<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

use yii\bootstrap5\Html;
use yii\grid\GridView;
use yii\widgets\LinkPager;

$this->title = 'Comments';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="comment-index">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'pager' => [
            'class' => LinkPager::class,
            'options' => ['class' => 'pagination justify-content-center'],
            'linkOptions' => ['class' => 'page-link'],
            'activePageCssClass' => 'active',
            'disabledPageCssClass' => 'disabled',
            'prevPageLabel' => '<i class="bi bi-chevron-left"></i>',
            'nextPageLabel' => '<i class="bi bi-chevron-right"></i>',
        ],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            
            [
                'attribute' => 'article_id',
                'label' => 'Article',
                'value' => function ($model) {
                    return $model->article ? Html::a(
                        Html::encode($model->article->title),
                        ['article/view', 'slug' => $model->article->slug],
                        ['target' => '_blank']
                    ) : '-';
                },
                'format' => 'raw',
            ],
            [
                'attribute' => 'user_id',
                'label' => 'User',
                'value' => function ($model) {
                    return $model->user ? Html::encode($model->user->username) : '<span class="text-muted">Guest</span>';
                },
                'format' => 'raw',
            ],
            [
                'attribute' => 'content',
                'value' => function ($model) {
                    return \yii\helpers\StringHelper::truncate(Html::encode($model->content), 100);
                },
            ],
            [
                'attribute' => 'status',
                'value' => function ($model) {
                    $statusLabels = [
                        'pending' => '<span class="badge bg-warning">Pending</span>',
                        'approved' => '<span class="badge bg-success">Approved</span>',
                        'rejected' => '<span class="badge bg-danger">Rejected</span>',
                    ];
                    return $statusLabels[$model->status] ?? $model->status;
                },
                'format' => 'raw',
            ],
            [
                'attribute' => 'parent_id',
                'label' => 'Type',
                'value' => function ($model) {
                    return $model->parent_id ? '<span class="badge bg-info">Reply</span>' : '<span class="badge bg-primary">Comment</span>';
                },
                'format' => 'raw',
            ],
            [
                'attribute' => 'created_at',
                'format' => 'raw',
                'value' => function ($model) {
                    return date('Y-m-d', $model->created_at) . '<br>' . 
                           '<small class="text-muted">' . date('H:i', $model->created_at) . '</small>';
                },
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'header' => 'Actions',
                'template' => '{view} {update} {delete}',
                'buttons' => [
                    'view' => function ($url, $model) {
                        return Html::a('<i class="bi bi-eye"></i>', $url, [
                            'title' => 'View',
                            'class' => 'btn btn-sm btn-outline-primary',
                        ]);
                    },
                    'update' => function ($url, $model) {
                        return Html::a('<i class="bi bi-pencil"></i>', $url, [
                            'title' => 'Update Status',
                            'class' => 'btn btn-sm btn-outline-warning',
                        ]);
                    },
                    'delete' => function ($url, $model) {
                        return Html::a('<i class="bi bi-trash"></i>', $url, [
                            'title' => 'Delete',
                            'class' => 'btn btn-sm btn-outline-danger',
                            'data' => [
                                'confirm' => 'Are you sure you want to delete this comment?',
                                'method' => 'post',
                            ],
                        ]);
                    },
                ],
            ],
        ],
    ]); ?>
</div>

