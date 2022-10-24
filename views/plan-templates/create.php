<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\PlanTemplate */

$this->title = Yii::t('app', 'Create Plan Template');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Plan Templates'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="plan-template-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
