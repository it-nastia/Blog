<?php

/** @var yii\web\View $this */
/** @var app\models\Category $model */
/** @var yii\bootstrap5\ActiveForm $form */

?>

<div class="category-form">
    <?php $form = \yii\bootstrap5\ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'slug')->textInput(['maxlength' => true])
        ->hint('Leave empty to auto-generate from name') ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 4]) ?>

    <?= $form->field($model, 'image')->textInput(['maxlength' => true])
        ->hint('Enter image URL (e.g., https://example.com/image.jpg)') ?>

    <div class="form-group mt-3">
        <?= \yii\bootstrap5\Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
        <?= \yii\bootstrap5\Html::a('Cancel', ['index'], ['class' => 'btn btn-secondary']) ?>
    </div>

    <?php \yii\bootstrap5\ActiveForm::end(); ?>
</div>

