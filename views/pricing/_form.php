<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper; 
use app\models\Stop; 
use app\models\Route; 

/* @var $this yii\web\View */
/* @var $model app\models\Pricing */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="pricing-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'start')->widget(\yii2mod\selectize\Selectize::className(), [
        'items'=>ArrayHelper::map(Stop::find()->all(), 'name', function($model) {
            return $model->name;
        }),
        'pluginOptions' => [
            'persist' => false,
            'createOnBlur' => true,
        ]
    ]) ?>
    
    <?= $form->field($model, 'end')->widget(\yii2mod\selectize\Selectize::className(), [
        'items'=>ArrayHelper::map(Stop::find()->all(), 'name', function($model) {
            return $model->name;
        }),
        'pluginOptions' => [
            'persist' => false,
            'createOnBlur' => true,
        ]
    ]) ?>

    <?= $form->field($model, 'route')->widget(\yii2mod\selectize\Selectize::className(), [
        'items'=>ArrayHelper::map(Route::find()->where(['parent'=>null])->orderBy('idx ASC')->all(), 'id', function($model) {
            return  $model->start.' - '.$model->end;
        }),
        'pluginOptions' => [
            'persist' => false,
            'createOnBlur' => true,
            'create' => false
        ]
    ]) ?>

    <?= $form->field($model, 'price', [
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
    
<?= $form->field($model, 'currency')->dropDownList([
        'TZS'=>'Tanzanian Shillings',
        'UGS' =>'Ugandan Shillings',
        'RWF'=>'Rwandan Francs',
        'USD'=>'US Dollars',
        'FIB'=>'Burundian Francs',
        'KSH'=>'Kenyan Shillings',
    ]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
