<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\GenPlanned */
/* @var $form ActiveForm */

$this->title = 'Generate Planned Traffic';
?>
<div class='plan-templates-_selmonth'>

    <?php $form = ActiveForm::begin(); ?>

        <div class='row'>
            <div class='col-md-4'><?= $form->field($model, 'month')->dropDownList([
                '1' => 'January', '2' => 'February', '3' => 'March', '4' => 'April',
                '5' => 'May', '6' => 'June', '7' => 'July', '8' => 'August',
                '9' => 'September', '10' => 'October', '11' => 'November', '12' => 'December',
            ]) ?></div>
            <div class='col-md-4'></div>
            <div class='col-md-4'></div>
        </div>
    
        <div class='form-group'>
            <?= Html::submitButton(Yii::t('app', 'Generate'), ['class' => 'btn btn-primary']) ?>
        </div>
    <?php ActiveForm::end(); ?>

</div><!-- plan-templates-_selmonth -->
