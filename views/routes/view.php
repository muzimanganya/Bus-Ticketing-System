<?php

use yii\helpers\Html;
use app\models\UploadPricing;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Route */

$this->title = $model->start.' - '.$model->end;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Routes'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="route-view">

    <?php if($model->parent == null) : ?>
        <?= $this->render('_uploadPricing', ['model'=>new UploadPricing(['route'=>$model->id]), 'route'=>$model->id]) ?>
    <?php endif; ?>
        
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
            'name',
            'parent',
            'start',
            'end',
            'idx',
            'send_sms:boolean',
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
           'has_promotion:boolean',
           'isInternational:boolean',
           'customer_care',
        ],
    ]) ?>

</div>
