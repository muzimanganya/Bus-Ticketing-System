<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper; 
use app\models\Stop;
use app\models\Bus;
use app\models\Route;

/* @var $this yii\web\View */
/* @var $model app\models\Route */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="route-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name') ?>
   
   <?= $form->field($model, 'parent')->widget(\yii2mod\selectize\Selectize::className(), [
        'items'=>ArrayHelper::merge([null=>'Parent Route'],  ArrayHelper::map(Route::find()->where(['parent'=>null])->all(), 'id', function($model) {
            return $model->start.' - '.$model->end;
        })),
        'pluginOptions' => [
            'persist' => false,
            'createOnBlur' => true,
            'create' => true
        ]
    ]) ?>

    <?= $form->field($model, "start")->widget(\yii2mod\selectize\Selectize::className(), [
        'items'=>ArrayHelper::map(Stop::find()->all(), 'name', function($model) {
            return $model->name;
        }),
        'pluginOptions' => [
            'persist' => false,
            'createOnBlur' => true,
            'create' => true
        ]
    ]) ?>

    <?= $form->field($model, "end")->widget(\yii2mod\selectize\Selectize::className(), [
        'items'=>ArrayHelper::map(Stop::find()->all(), 'name', function($model) {
            return $model->name;
        }),
        'pluginOptions' => [
            'persist' => false,
            'createOnBlur' => true,
            'create' => true
        ]
    ]) ?>
     
    <div class="row">
        <div class='col-md-4'>
            <?= $form->field($model, 'send_sms')->dropDownList($model->sendSMS) ?> 
        </div>
        
        <div class='col-md-4'>
            <?= $form->field($model, 'has_promotion')->dropDownList([0=>'No Promotion', 1=>'Has Promotion']) ?> 
        </div>
        
        <div class='col-md-4'>
            <?= $form->field($model, 'is_intl')->dropDownList([0=>'Local Route', 1=>'International Route']) ?>  <!-- Has Seat should be added --> 
        </div>
   </div>
   
    <div class="row">
        <div class='col-md-3'>
            <?= $form->field($model, 'is_return')->dropDownList([0=>'Go Route', 1=>'Return Route']) ?> 
        </div>
        
        <div class='col-md-4'>
           <?= $form->field($model, 'return')->widget(\yii2mod\selectize\Selectize::className(), [
                'items'=>ArrayHelper::merge([null=>'Respective Return Route'],  ArrayHelper::map(Route::find()->where(['parent'=>null])->all(), 'id', function($model) {
                    return $model->start.' - '.$model->end;
                })),
                'pluginOptions' => [
                    'persist' => false,
                    'createOnBlur' => true,
                    'create' => false
                ]
            ]) ?>
        </div>
        
        <div class='col-md-2'>
            <?= $form->field($model, 'idx')->textInput() ?>
        </div>
        
        <div class='col-md-3'>
            <?= $form->field($model, 'customer_care')->textInput(['maxlength' => true]) ?>    
        </div>
   </div>
   
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
