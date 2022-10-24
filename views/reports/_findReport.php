<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\FindReport */
/* @var $form ActiveForm */
?>
<div class="reports-_findReport">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'report')->dropDownList([
        'planned' => 'Planned Routes', //Format: action id=>name 
        'sales' => 'Daily Sales',
        'user-sales' => 'User Sales',
        'pos-sales' => 'POS Sales',
        'card-sales' => 'Card Sales',
        'cashier-tickets' => 'Cashier Tickets',
        'customer-tickets' => 'Customer Tickets',
        'bus-report' => 'Bus Report',
        'booking' => 'Daily Booking',
        'promotion' => 'Daily Promotions',
        'mobile-tickets' => 'Mobile Tickets(LUMICASH)',
        'logs' => 'Daily Logs',
    ]) ?>

    <div class='row'>
        <div class='col-md-6'>
            <?= $form->field($model, 'start')->widget(\yii\jui\DatePicker::classname(), [
                'options' => ['class' => 'form-control'],
                //'language' => 'ru',
                'dateFormat' => 'yyyy-MM-dd',
            ]) ?>
        </div>
        <div class='col-md-6'>
            <?= $form->field($model, 'end')->widget(\yii\jui\DatePicker::classname(), [
                'options' => ['class' => 'form-control'],
                //'language' => 'ru',
                'dateFormat' => 'yyyy-MM-dd',
            ]) ?>
        </div>
    </div>

    <?= $form->field($model, 'reference') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>

</div><!-- reports-_findReport -->