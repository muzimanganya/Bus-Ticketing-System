<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\ChangeCustomerBus */
/* @var $form ActiveForm */
$this->title = 'Move Customer to Bus';

$this->registerJs('
    $(".btn-info").on("click", function(e){
        //load customer info
        var ticket = $("#ticket").val();
        $.getJSON( "'.Url::to(['ticket-details']).'",{
            "ticket":ticket
        })  
        .done(function(data) { 
            console.log(data);
            $(".well").html(data.message);         
        });
    });
');
?>
<div class="site-_moveCustomer">
    <div class='well well-sm'></div>
    
    <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'ticket', ['inputOptions'=>['id'=>'ticket', 'class'=>'form-control']]) ?>
        
        <?= $form->field($model, 'to_date')->widget(\yii\jui\DatePicker::classname(), [
                'dateFormat' => 'yyyy-MM-dd',
                'options' => ['class' => 'form-control']
            ]) ?>
        
        <?= $form->field($model, 'to_time')->widget(\yii\widgets\MaskedInput::className(), [
            'mask' => '99H99',
        ]) ?>
        <?= $form->field($model, 'seat') ?>
        <?= $form->field($model, 'comment')->textArea() ?>
    
        <div class="form-group">
            <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-primary']) ?>
            <?= Html::button(Yii::t('app', 'Load'), ['class' => 'btn btn-info']) ?>
        </div>
    <?php ActiveForm::end(); ?>

</div><!-- site-_moveCustomer -->
