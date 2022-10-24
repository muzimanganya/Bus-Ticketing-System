<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\RouteCard */

$this->title = Yii::t('app', 'Update {modelClass}: ', [
    'modelClass' => 'Route Card',
]) . $model->card;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Route Cards'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->card, 'url' => ['view', 'id' => $model->card]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="route-card-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
