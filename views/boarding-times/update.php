<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\BoardingTime */

$this->title = Yii::t('app', 'Update {modelClass}: ', [
    'modelClass' => 'Boarding Time',
]) . $model->route;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Boarding Times'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->route, 'url' => ['view', 'route' => $model->route, 'start' => $model->start, 'end' => $model->end]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="boarding-time-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
