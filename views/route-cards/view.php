<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\RouteCard */

$this->title = $model->card;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Route Cards'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="route-card-view">
    <p class='pull-right'>
        <!-- ?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->card], ['class' => 'btn btn-primary']) ?>
        <!--?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->card], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?-->
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'card', 
            'start',
            'end',
            'price',
            'currency',
            'total_trips',
	    'phone',
            'owner',
            'created_at:datetime',
            'created_by',
            'updated_at:datetime',
            'updated_by',
        ],
    ]) ?>

</div>
