<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Bus */

$this->title = Yii::t('app', 'Create Bus');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Buses'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bus-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
