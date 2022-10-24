<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\PlannedRoute */

$this->title = Yii::t('app', 'Plan a Route');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Planned Routes'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="planned-route-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
