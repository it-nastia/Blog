<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

use yii\bootstrap5\Html;
use yii\grid\GridView;
use yii\widgets\LinkPager;

$this->title = 'Categories';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="category-index">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <?= Html::a('Create Category', ['create'], ['class' => 'btn btn-success']) ?>
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
            
            'name',
            'slug',
            [
                'attribute' => 'description',
                'value' => function ($model) {
                    return $model->description ? \yii\helpers\StringHelper::truncate($model->description, 50) : '-';
                },
            ],
            [
                'attribute' => 'articlesCount',
                'label' => 'Articles',
                'value' => function ($model) {
                    return $model->getArticlesCount();
                },
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
                            'title' => 'Update',
                            'class' => 'btn btn-sm btn-outline-warning',
                        ]);
                    },
                    'delete' => function ($url, $model) {
                        return Html::a('<i class="bi bi-trash"></i>', $url, [
                            'title' => 'Delete',
                            'class' => 'btn btn-sm btn-outline-danger',
                            'data' => [
                                'confirm' => 'Are you sure you want to delete this category?',
                                'method' => 'post',
                            ],
                        ]);
                    },
                ],
            ],
        ],
    ]); ?>
</div>

