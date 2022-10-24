<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\PlanTemplate */

$this->title = Yii::t('app', 'Update {modelClass}: ', [
    'modelClass' => 'Plan Template',
]) . $model->route;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Plan Templates'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->route, 'url' => ['view', 'route' => $model->route, 'bus' => $model->bus, 'hour' => $model->hour]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="plan-template-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
