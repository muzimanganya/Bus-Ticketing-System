<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\PlannedRoute */

$this->title = "{$model->routeR->routeName} - {$model->dept_date}, {$model->dept_time} ";

$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Planned Routes'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="planned-route-view">

    <p>
        <?= Html::a(Yii::t('app', 'Update'), ['update', 'route' => $model->route, 'dept_date' => $model->dept_date, 'dept_time' => $model->dept_time, 'bus' => $model->bus], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'route' => $model->route, 'dept_date' => $model->dept_date, 'dept_time' => $model->dept_time, 'bus' => $model->bus], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [ 
            [
				'attribute'=>'route', 
				$model->routeR->routeName  
            ],
            'dept_date',
            'dept_time',
            'bus',
            'priority',
            'capacity',
            'created_at:datetime',
            [
				'attribute'=>'created_by', 
				$model->createdBy->name 
            ],
            'updated_at:datetime', 
            [
				'attribute'=>'updated_by', 
				$model->updatedBy->name 
            ],
            'is_active:boolean',
        ],
    ]) ?>

</div>
