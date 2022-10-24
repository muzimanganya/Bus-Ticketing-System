<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Staff */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Staff'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="staff-view">  

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'mobile',
            'name',
            'location',
            'staffRole',
            'created_at:date', 
            'is_active:boolean',
        ],
    ]) ?>

</div>
