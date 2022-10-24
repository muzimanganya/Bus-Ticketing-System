<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Bus */

$this->title = $model->regno;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Buses'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bus-view">

    <p class='pull-right'>
        <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->regno], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->regno], [
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
            'regno',
            'leftseats',
            'rightseats',
            'backseats',
            'doorSide',
            [
                'attribute'=>'driver',
                'value'=>function($model)
                {
                    return "{$model->driverR->name} - {$model->driver}";
                }
            ],
            'total_seats',
            'created_at:date',
            'created_by',
            'updated_at:date',
            'updated_by',
        ],
    ]) ?>

</div>

<?= \app\widgets\Bus::widget(['model'=>$model]) ?>