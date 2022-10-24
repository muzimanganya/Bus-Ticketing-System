<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Pricing */

$this->title = Yii::t('app', 'Update {modelClass}: ', [
    'modelClass' => 'Pricing',
]) . $model->start;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Pricings'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->start, 'url' => ['view', 'start' => $model->start, 'end' => $model->end, 'route' => $model->route, 'currency' => $model->currency]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="pricing-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
