<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use app\models\Staff;

/* @var $this yii\web\View */
/* @var $model app\models\Wallet */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="wallet-form">

    <?php $form = ActiveForm::begin(); ?>
    
    <?= $form->field($model, 'owner')->widget(\yii2mod\selectize\Selectize::className(), [
        'items'=>ArrayHelper::map(Staff::find()->where(['role'=>'mobile'])->all(), 'mobile', function($model) {
            return $model->name;
        }),
        'pluginOptions' => [
            'persist' => false,
            'createOnBlur' => false,
            'create' => true
        ]
    ]) ?>
    
    <?= $form->field($model, 'currency')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'current_amount')->textInput() ?>

    <?= $form->field($model, 'last_recharged')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
