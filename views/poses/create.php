<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\POS */

$this->title = Yii::t('app', 'New Machine');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'POS Machines'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pos-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
