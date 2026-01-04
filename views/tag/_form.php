<?php

/** @var yii\web\View $this */
/** @var app\models\Tag $model */
/** @var yii\bootstrap5\ActiveForm $form */

?>

<div class="tag-form">
    <?php $form = \yii\bootstrap5\ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'slug')->textInput(['maxlength' => true])
        ->hint('Leave empty to auto-generate from name') ?>

    <div class="form-group mt-3">
        <?= \yii\bootstrap5\Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
        <?= \yii\bootstrap5\Html::a('Cancel', ['index'], ['class' => 'btn btn-secondary']) ?>
    </div>

    <?php \yii\bootstrap5\ActiveForm::end(); ?>
</div>

