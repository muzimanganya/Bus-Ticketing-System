<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\BusSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="bus-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'regno') ?>

    <?= $form->field($model, 'leftseats') ?>

    <?= $form->field($model, 'rightseats') ?>

    <?= $form->field($model, 'backseats') ?>

    <?= $form->field($model, 'doorside') ?>

    <?php // echo $form->field($model, 'driver') ?>

    <?php // echo $form->field($model, 'total_seats') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'created_by') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <?php // echo $form->field($model, 'updated_by') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
