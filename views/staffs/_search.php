<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\StaffSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="staff-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'mobile') ?>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'location') ?>

    <?= $form->field($model, 'role') ?>

    <?= $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'created_by') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <?php // echo $form->field($model, 'updated_by') ?>

    <?php // echo $form->field($model, 'timezone') ?>

    <?php // echo $form->field($model, 'password') ?>

    <?php // echo $form->field($model, 'auth_key') ?>

    <?php // echo $form->field($model, 'password_hash') ?>

    <?php // echo $form->field($model, 'is_actve') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
