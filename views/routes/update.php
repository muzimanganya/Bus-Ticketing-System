<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Route */

$this->title = Yii::t('app', 'Update {modelClass}: ', [
    'modelClass' => 'Route',
]) . $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Routes'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->routeName, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="route-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
