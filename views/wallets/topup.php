<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use app\models\Wallet;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\TopUp */
/* @var $form ActiveForm */
?>
<div class="wallets-topup">

    <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'wallet')->widget(\yii2mod\selectize\Selectize::className(), [
            'items'=>ArrayHelper::map(Wallet::find()->all(), 'id', function($model) {
                return $model->ownerR->name.' '.$model->currency;
            }),
            'pluginOptions' => [
                'persist' => false,
                'createOnBlur' => false,
                'create' => true
            ]
        ]) ?>
    
        <?= $form->field($model, 'amount') ?>
        
        <?= $form->field($model, 'reference') ?>
    
        <div class="form-group">
            <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-primary']) ?>
        </div>
    <?php ActiveForm::end(); ?>

</div><!-- wallets-topup -->
