<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\ThirdParty */

$this->title = Yii::t('app', 'Update {modelClass}: ', [
    'modelClass' => 'TCompany',
]) . $model->tenant;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Third Parties'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->tenant, 'url' => ['view', 'tenant' => $model->tenant, 'database' => $model->database]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="third-party-update">


    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
