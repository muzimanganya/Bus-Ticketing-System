<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\UploadPricing */
/* @var $form ActiveForm */
?>
<div class="routes-_uploadPricing">
    <div class='row'>
        <div class='col-md-6'>
            <div class='alert alert-info'>The csv must have FROM-TO, PRICE, CURRENCY format</div>
        </div>
        
        <div class='col-md-6'>
        </div>
    </div>
    
    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data'], 
            'method'=>'POST', 
            'action'=>['upload-pricing']
    ]); ?>
        <div class='row'>
            <div class='col-md-3'>
                <?= $form->field($model, 'file')->fileInput()->label(false) ?>
            </div>
            
            <div class='col-md-2'>
                <?= $form->field($model, 'route')->hiddenInput()->label(false) ?>
            </div>
            
            <div class='col-md-1'><br />
                <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-primary']) ?>
           </div>
            
            <div class='col-md-6'>
            </div>
        </div>
    
    <?php ActiveForm::end(); ?>

</div><!-- routes-_uploadPricing -->
