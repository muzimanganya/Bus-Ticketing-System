<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\RouteCard */

$this->title = Yii::t('app', 'Add a card');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Route Cards'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="route-card-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
