<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Login'; 
?>  

<div id="login"> 

    <h2><span class="fontawesome-lock"></span>Sign In</h2>

    <?php $form = ActiveForm::begin([
        'id' => 'login-form',
        'layout' => 'horizontal',
        /*'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-lg-3\">{input}</div>\n<div class=\"col-lg-8\">{error}</div>",
            'labelOptions' => ['class' => 'col-lg-1 control-label'],
        ],*/
    ]); ?>

        <fieldset>
            <p><?= $form->field($model, 'username')->textInput(['autofocus' => true, 'id'=>'email', 'placeholder'=>'Mobile Number'])->label(false) ?></p>
            
            <p><?= $form->field($model, 'password')->passwordInput(['id'=>'password', 'placeholder'=>'Your Password'])->label(false) ?> </p>
            
            <?= Html::submitButton('Sign In', ['class' => 'btn btn-primary pull-right', 'name' => 'login-button']) ?>
        </fieldset>

    <?php ActiveForm::end(); ?>

</div> <!-- end login --> 