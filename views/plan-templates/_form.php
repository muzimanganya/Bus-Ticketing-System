<?php


use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper; 
use app\models\Stop; 
use app\models\Route; 
use app\models\Bus; 

/* @var $this yii\web\View */
/* @var $model app\models\PlanTemplate */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="plan-template-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, "route")->widget(\yii2mod\selectize\Selectize::className(), [
        'items'=>ArrayHelper::map(Route::find()->where(['parent'=>null])->orderBy('idx ASC')->all(), 'id', function($model) {
            return  $model->name.' ('.$model->start.' - '.$model->end.')';
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
    
     <?= $form->field($model, 'hour')->widget(\yii\widgets\MaskedInput::className(), [
        'mask' => '99H99',
    ]) ?>

     <?= $form->field($model, 'pcapacity')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
