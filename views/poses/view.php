<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\POS */

$this->title = $model->serial;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'POS Machines'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pos-view">

    <p class="pull-right">
        <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->serial], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->serial], [
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
            'serial',
            'mobile',
            'simcard:integer',
            'location',
            'created_at:date',
            'created_by',
            'updated_at:date',
            'updated_by',
            'is_active:boolean',
        ],
    ]) ?>

</div>
