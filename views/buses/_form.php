<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use app\models\Staff;

/* @var $this yii\web\View */
/* @var $model app\models\Bus */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="bus-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'regno')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'leftseats')->textInput() ?>

    <?= $form->field($model, 'rightseats')->textInput() ?>

    <?= $form->field($model, 'backseats')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'doorside')->dropDownList($model->sides) ?>

    <?= $form->field($model, "driver")->widget(\yii2mod\selectize\Selectize::className(), [
        'items'=>ArrayHelper::map(Staff::find()->where(['role'=>'driver', 'is_active'=>1])->all(), 'mobile', function($model) {
            return $model->name;
        }),
        'pluginOptions' => [
            'persist' => false,
            'createOnBlur' => true,
            'create' => true
        ]
    ]) ?>

    <?= $form->field($model, 'total_seats')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
