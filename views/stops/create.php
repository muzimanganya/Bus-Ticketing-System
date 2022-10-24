<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Stop */

$this->title = Yii::t('app', 'Create Stop');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Stops'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="stop-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
