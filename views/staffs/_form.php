<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Staff */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="staff-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'mobile')->textInput() ?>

    <?= $form->field($model, 'location')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'role')->dropDownList($model->getRoles()) ?>

    <?= $form->field($model, 'password')->passwordInput(['maxlength' => true]) ?> 

    <?= $form->field($model, 'repeat_password')->passwordInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'is_active')->dropDownList([0=>Yii::t('app', 'Suspended'), 1=>Yii::t('app', 'Active')]) ?>
    
    <?= $form->field($model, 'tenant_db')->dropDownList(\yii\helpers\ArrayHelper::map(Yii::$app->db->createCommand('SELECT * FROM TenantMapping')->queryAll(), 'database', 'tenant')) ?>
    
    <?= $form->field($model, 'timezone')->dropDownList([
            'Africa/Kigali'=>'Rwanda/Burundi Time',
            'Africa/Kampala'=>'Tanzania/Kenya/Uganda Time', 
    ]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
