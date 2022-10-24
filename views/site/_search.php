<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper; 
use app\models\Stop; 
use app\models\Route; 
use app\models\Bus; 


$this->registerJs("
    $('#form').hide();
            
    $('#results-filter').on('click', function(){
        var label = $(this).html().toLowerCase();
        if(label=='filter')
        {
            //show form
            $('#form').show();
            //change label
            $(this).html('Hide Filter');
        }
        else
        {
            //hide form
            $('#form').hide();
            //change label
            $(this).html('Filter');
        }
    });
");

/* @var $this yii\web\View */
/* @var $model app\models\Ticket */
/* @var $form ActiveForm */
?>
<div class="site-search">
    <?= Html::button('Filter', ['id'=>'results-filter', 'class'=>'btn btn-info pull-right']) ?>
    <?php $form = ActiveForm::begin(['layout' => 'horizontal', 'id'=>'form', 'method'=>'get','action' => ['index'],]); ?>

        <?= $form->field($model, "route")->widget(\yii2mod\selectize\Selectize::className(), [
        'items'=>ArrayHelper::merge([''=>'Select Main Route'], ArrayHelper::map(Route::find()->where(['parent'=>null])->orderBy('idx ASC')->all(), 'id', function($model) {
                return $model->start.' - '.$model->end;
            })),
            'pluginOptions' => [
                'persist' => false,
                'createOnBlur' => true,
                'create' => true
            ]
        ]) ?>

        <?= $form->field($model, "bus")->widget(\yii2mod\selectize\Selectize::className(), [
            'items'=>ArrayHelper::merge([''=>'Select Bus'], ArrayHelper::map(Bus::find()->all(), 'regno', function($model) {
                return $model->regno;
            })),
            'pluginOptions' => [
                'persist' => false,
                'createOnBlur' => true,
                'create' => true
            ]
        ]) ?>
        
        <div class="row">
            <div class="col-md-2"></div>
            <div class="col-md-5">
                 <?= $form->field($model, 'dept_date')->widget(\yii\jui\DatePicker::classname(), [
                    'dateFormat' => 'yyyy-MM-dd',
                    'options' => ['class' => 'form-control']
                ]) ?>

             </div>

             <div class="col-md-2">
                <?= $form->field($model, 'dept_time')->widget(\yii\widgets\MaskedInput::className(), [
                    'mask' => '99H99',
                ]) ?>
            </div>
            <div class="col-md-3"></div>
        </div>
    
        <div class="row">
            <div class="col-md-4"></div>
            <div class="col-md-5">
                <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-primary pull-right']) ?>
            </div>
            <div class="col-md-3"></div>
        </div>
    <?php ActiveForm::end(); ?>

</div><!-- site-_search -->
