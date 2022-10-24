<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Wallet */

$this->title = "{$model->ownerR->name} - {$model->currency}";
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Wallets'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="wallet-view">

    <p class='pull-right'>
        <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
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
            'id',
            'currency',
            [
                'attribute'=>'owner',
                'value'=>function($model)
                {
                    return "{$model->ownerR->name}";
                }
            ],
            'current_amount:integer',
            'last_recharged:integer',
            'created_at:date', 
            [
                'attribute'=>'created_by',
                'value'=>function($model)
                {
                    return "{$model->createdBy->name}";
                }
            ],
            'updated_at:date', 
            [
                'attribute'=>'updated_by',
                'value'=>function($model)
                {
                    return "{$model->updatedBy->name}";
                }
            ],
        ],
    ]) ?>

</div>
