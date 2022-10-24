<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\CapacityChangeForm */
/* @var $form ActiveForm */
?>
<div class="site-_changeRouteCapacity">

    <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'capacity') ?>
        <?= $form->field($model, 'comment')->textArea() ?>
        <?= $form->field($model, 'pk')->hiddenInput()->label(false) ?>
    <?php ActiveForm::end(); ?>

</div><!-- site-_changeRouteCapacity -->
