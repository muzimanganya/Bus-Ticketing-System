<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;  
use app\models\Route; 
use app\models\Stop; 

/* @var $this yii\web\View */
/* @var $model app\models\BoardingTime */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="boarding-time-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, "route")->widget(\yii2mod\selectize\Selectize::className(), [
        'items'=>ArrayHelper::map(Route::find()->where(['parent'=>null])->orderBy('idx ASC')->all(), 'id', function($model) {
            return  $model->name.' ('.$model->start.' - '.$model->end.')';
        }),
        'pluginOptions' => [
            'persist' => false,
            'createOnBlur' => true,
            'create' => false
        ]
    ]) ?>

    <?= $form->field($model, 'start')->widget(\yii2mod\selectize\Selectize::className(), [
        'items'=>ArrayHelper::map(Stop::find()->all(), 'name', 'name'),
        'pluginOptions' => [
            'persist' => false,
            'createOnBlur' => true,
            'create' => false
        ]
    ]) ?>

    <?= $form->field($model, 'end')->widget(\yii2mod\selectize\Selectize::className(), [
        'items'=>ArrayHelper::map(Stop::find()->all(), 'name', 'name'),
        'pluginOptions' => [
            'persist' => false,
            'createOnBlur' => true,
            'create' => false
        ]
    ]) ?>

    <?= $form->field($model, 'offset')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
