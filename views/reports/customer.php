<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper; 
use app\models\Stop; 
use app\models\Route; 
use app\models\Bus; 

/* @var $this yii\web\View */
/* @var $model app\models\PlannedRoute */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="planned-route-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, "route")->widget(\yii2mod\selectize\Selectize::className(), [
        'items'=>ArrayHelper::map(Route::find()->where(['parent'=>null])->orderBy('idx ASC')->all(), 'id', function($model) {
            return $model->start.' - '.$model->end;
        }),
        'pluginOptions' => [
            'persist' => false,
            'createOnBlur' => true,
            'create' => true
        ]
    ]) ?>

    <?= $form->field($model, "bus")->widget(\yii2mod\selectize\Selectize::className(), [
        'items'=>ArrayHelper::map(Bus::find()->all(), 'regno', function($model) {
            return $model->regno;
        }),
        'pluginOptions' => [
            'persist' => false,
            'createOnBlur' => true,
            'create' => true
        ]
    ]) ?>
    
    <div class="row">
        <div class="col-md-4">
             <?= $form->field($model, 'dept_date')->widget(\yii\jui\DatePicker::classname(), [
                'dateFormat' => 'yyyy-MM-dd',
                'options' => ['class' => 'form-control']
            ]) ?>

         </div>

         <div class="col-md-4">
            <?= $form->field($model, 'dept_time')->widget(\yii\widgets\MaskedInput::className(), [
                'mask' => '99H99',
            ]) ?>
        </div>

         <div class="col-md-4">
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' =>'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
