<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Staff */

$this->title = Yii::t('app', 'New Staff');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Staffs'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="staff-create"> 

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
