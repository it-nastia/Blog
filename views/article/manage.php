<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

use yii\bootstrap5\Html;
use yii\grid\GridView;

$this->title = 'Manage Articles';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="article-manage">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <?= Html::a('Create Article', ['create'], ['class' => 'btn btn-success']) ?>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            
            [
                'attribute' => 'image',
                'format' => 'raw',
                'value' => function ($model) {
                    if ($model->image) {
                        return Html::img($model->image, ['style' => 'max-width: 50px; max-height: 50px; object-fit: cover;']);
                    }
                    return '<span class="text-muted">No image</span>';
                },
                'contentOptions' => ['style' => 'width: 80px;'],
            ],
            
            'title',
            [
                'attribute' => 'category_id',
                'value' => function ($model) {
                    return $model->category ? Html::encode($model->category->name) : '-';
                },
            ],
            [
                'attribute' => 'status',
                'value' => function ($model) {
                    $statusLabels = [
                        'draft' => '<span class="badge bg-secondary">Draft</span>',
                        'published' => '<span class="badge bg-success">Published</span>',
                    ];
                    return $statusLabels[$model->status] ?? $model->status;
                },
                'format' => 'raw',
            ],
            [
                'attribute' => 'views',
                'label' => 'Views',
            ],
            [
                'attribute' => 'tags',
                'value' => function ($model) {
                    if (empty($model->tags)) {
                        return '<span class="text-muted">No tags</span>';
                    }
                    $tagNames = [];
                    foreach ($model->tags as $tag) {
                        $tagNames[] = Html::encode($tag->name);
                    }
                    return implode(', ', $tagNames);
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
                'template' => '{view} {update} {delete}',
                'urlCreator' => function ($action, $model, $key, $index) {
                    if ($action === 'view') {
                        return ['view', 'slug' => $model->slug];
                    }
                    return [$action, 'id' => $model->id];
                },
                'buttons' => [
                    'view' => function ($url, $model) {
                        return Html::a('<i class="bi bi-eye"></i>', $url, [
                            'title' => 'View',
                            'class' => 'btn btn-sm btn-outline-primary',
                            'target' => '_blank',
                        ]);
                    },
                    'update' => function ($url, $model) {
                        return Html::a('<i class="bi bi-pencil"></i>', $url, [
                            'title' => 'Update',
                            'class' => 'btn btn-sm btn-outline-warning',
                        ]);
                    },
                    'delete' => function ($url, $model) {
                        return Html::a('<i class="bi bi-trash"></i>', $url, [
                            'title' => 'Delete',
                            'class' => 'btn btn-sm btn-outline-danger',
                            'data' => [
                                'confirm' => 'Are you sure you want to delete this article?',
                                'method' => 'post',
                            ],
                        ]);
                    },
                ],
            ],
        ],
    ]); ?>
</div>

