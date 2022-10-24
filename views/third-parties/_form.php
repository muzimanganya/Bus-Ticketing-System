<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\ThirdParty */
/* @var $form yii\widgets\ActiveForm */

$dbs = Yii::$app->db->createCommand('SHOW DATABASES')
            ->queryColumn();

 $dbList = [];
 foreach ($dbs as $db) {
 	if($db=='information_schema' || $db == 'performance_schema' || $db == 'mysql'|| $db == 'root'|| $db == 'phpmyadmin')
 		continue;

 	$dbList[$db] = $db;
 }
?>

<div class="third-party-form">

    <?php $form = ActiveForm::begin(); ?>

	<?= $form->field($model, 'logoFile')->fileInput() ?>

    <?= $form->field($model, 'tenant')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'database')->dropDownList($dbList) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
