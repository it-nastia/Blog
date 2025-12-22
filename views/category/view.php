<?php

/** @var yii\web\View $this */
/** @var app\models\Category $model */

use yii\bootstrap5\Html;

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Categories', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="category-view">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1><?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-warning']) ?>
            <?= Html::a('Delete', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Are you sure you want to delete this category?',
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
                    <th>Name</th>
                    <td><?= Html::encode($model->name) ?></td>
                </tr>
                <tr>
                    <th>Slug</th>
                    <td><?= Html::encode($model->slug) ?></td>
                </tr>
                <tr>
                    <th>Description</th>
                    <td><?= $model->description ? nl2br(Html::encode($model->description)) : '<span class="text-muted">No description</span>' ?></td>
                </tr>
                <tr>
                    <th>Image</th>
                    <td>
                        <?php if ($model->image): ?>
                            <img src="<?= Html::encode($model->image) ?>" alt="<?= Html::encode($model->name) ?>" 
                                 style="max-width: 300px; max-height: 300px; object-fit: cover;" class="img-thumbnail">
                            <br><br>
                            <a href="<?= Html::encode($model->image) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                View Full Size
                            </a>
                        <?php else: ?>
                            <span class="text-muted">No image</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>Articles Count</th>
                    <td><?= $model->getArticlesCount() ?></td>
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
</div>

