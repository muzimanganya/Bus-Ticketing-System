<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\POS */

$this->title = Yii::t('app', 'Update {modelClass}: ', [
    'modelClass' => 'Pos',
]) . $model->serial;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Pos'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->serial, 'url' => ['view', 'id' => $model->serial]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="pos-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
