<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\Route;

/* @var $this yii\web\View */
/* @var $model app\models\RouteCard */
/* @var $form yii\widgets\ActiveForm */ 
?>

<div class="route-card-form">

    <?php $form = ActiveForm::begin(); ?>
    
    <div class='row'>
        <div class='col-md-4'><?= $form->field($model, 'card')->textInput(['maxlength' => true]) ?></div>
        <div class='col-md-4'><?= $form->field($model, 'total_trips')->textInput() ?></div>
        <div class='col-md-2'><?= $form->field($model, 'price')->textInput() ?></div> 
        <div class='col-md-2'></div> 
    </div>
 
    <div class='row'>
        <div class='col-md-4'><?= $form->field($model, 'start')->textInput(['maxlength' => true]) ?></div>
        <div class='col-md-4'><?= $form->field($model, 'end')->textInput(['maxlength' => true]) ?></div>
        <div class='col-md-2'><?= $form->field($model, 'currency')->textInput(['maxlength' => true]) ?></div> 
        <div class='col-md-2'></div> 
    </div>
    
    <div class='row'>
        <div class='col-md-12'><?= $form->field($model, 'multicard')->textArea() ?></div>
    </div>
    
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
