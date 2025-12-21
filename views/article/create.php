<?php

/** @var yii\web\View $this */
/** @var app\models\Article $model */
/** @var array $categories */
/** @var app\models\Tag[] $tags */

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Create Article';
$this->params['breadcrumbs'][] = ['label' => 'Articles', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="article-create">
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="article-form">
        <?php $form = ActiveForm::begin([
            'options' => ['enctype' => 'multipart/form-data']
        ]); ?>

        <div class="row">
            <div class="col-lg-8">
                <?= $form->field($model, 'title')->textInput(['maxlength' => true, 'placeholder' => 'Enter article title']) ?>

                <?= $form->field($model, 'slug')->textInput(['maxlength' => true, 'placeholder' => 'Auto-generated from title (optional)'])
                    ->hint('Leave empty to auto-generate from title') ?>

                <?= $form->field($model, 'content')->textarea(['rows' => 15, 'placeholder' => 'Write your article content here...']) ?>

                <?= $form->field($model, 'image')->textInput(['maxlength' => true, 'placeholder' => 'Image URL']) 
                    ->hint('Enter URL of the image (e.g., https://example.com/image.jpg)') ?>
            </div>

            <div class="col-lg-4">
                <?= $form->field($model, 'category_id')->dropDownList(
                    $categories,
                    ['prompt' => 'Select category...']
                ) ?>

                <?= $form->field($model, 'status')->dropDownList([
                    \app\models\Article::STATUS_DRAFT => 'Draft',
                    \app\models\Article::STATUS_PUBLISHED => 'Published',
                ]) ?>

                <div class="form-group">
                    <label class="form-label">Tags</label>
                    <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                        <?php foreach ($tags as $tag): ?>
                            <div class="form-check">
                                <?= Html::checkbox('Article[tagIds][]', false, [
                                    'value' => $tag->id,
                                    'id' => 'tag-' . $tag->id,
                                    'class' => 'form-check-input'
                                ]) ?>
                                <?= Html::label($tag->name, 'tag-' . $tag->id, ['class' => 'form-check-label']) ?>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($tags)): ?>
                            <p class="text-muted small">No tags available. Create tags first.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group mt-4">
            <?= Html::submitButton('Create Article', ['class' => 'btn btn-success']) ?>
            <?= Html::a('Cancel', ['index'], ['class' => 'btn btn-secondary']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

