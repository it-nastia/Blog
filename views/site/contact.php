<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Contact';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="site-contact">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success">
            <?= Yii::$app->session->getFlash('success') ?>
        </div>
    <?php else: ?>

    <p>
        If you have business inquiries or other questions, please fill out the following form to contact us.
    </p>

    <div class="row">
        <div class="col-lg-6">
            <?php $form = ActiveForm::begin(['id' => 'contact-form']); ?>

                <?= $form->field($model, 'name')->textInput(['autofocus' => true]) ?>

                <?= $form->field($model, 'email') ?>

                <?= $form->field($model, 'subject') ?>

                <?= $form->field($model, 'body')->textarea(['rows' => 6]) ?>

                <?php if (!YII_ENV_TEST): ?>
                    <?= $form->field($model, 'verifyCode')->widget(yii\captcha\Captcha::class) ?>
                <?php else: ?>
                    <?= $form->field($model, 'verifyCode')->textInput()->label('Verification Code') ?>
                <?php endif; ?>

                <div class="form-group">
                    <?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
                </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <?php endif; ?>
</div>
