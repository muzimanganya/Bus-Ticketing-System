<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use app\modules\mobile\models\Company;

$this->title = 'Top up';

/* @var $this yii\web\View */
/* @var $model app\modules\mobile\models\TopUp */
/* @var $form ActiveForm */
?>
<div class="mobile-money-topup">

    <?php $form = ActiveForm::begin(); ?>
    
        <?= $form->field($model, 'company')->widget(\yii2mod\selectize\Selectize::className(), [
            'items'=>ArrayHelper::map(Company::find()->all(), 'id', function($model) {
                return  $model->name;
            }),
            'pluginOptions' => [
                'persist' => false,
                'createOnBlur' => true,
                'create' => false
            ]
        ]) ?>   
        
        <?= $form->field($model, 'amount', [
                   'inputOptions' => ['placeholder' =>'Price', 'class' => 'form-control transparent']
             ])
            ->widget(\yii\widgets\MaskedInput::className(), [
                'clientOptions' => [
                    ['placeholder'=>'12344'],
                    'alias' => 'decimal',
                    'groupSeparator' => ',',
                    'autoGroup' => true,
                    'rightAlign'=>false,
                    'clearMaskOnLostFocus'=>true,
                    'removeMaskOnSubmit' => true
                ]
            ]) ?>
        
        <?= $form->field($model, 'reference') ?>
    
        <div class="form-group">
            <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-primary']) ?>
        </div>
    <?php ActiveForm::end(); ?>

</div><!-- mobile-money-topup -->
