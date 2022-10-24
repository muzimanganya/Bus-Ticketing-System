<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\BoardingTime */

$this->title = Yii::t('app', 'Create Boarding Time');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Boarding Times'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="boarding-time-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
