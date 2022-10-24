<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper; 
use app\models\Staff; 

/* @var $this yii\web\View */
/* @var $model app\models\POS */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="pos-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'serial')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'mobile')->textInput() ?>

    <?= $form->field($model, 'simcard')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'location')->textInput() ?>


    <?= $form->field($model, 'is_active')->dropDownList([0=>'Suspended', 1=>'Active']) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
