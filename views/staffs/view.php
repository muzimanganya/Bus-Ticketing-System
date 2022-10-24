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

    <p class='pull-right'> 
        <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->mobile], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->mobile], [
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
            'mobile',
            'name',
            'location',
            'staffRole',
            'created_at:date',
            'created_by',
            'updated_at:date',
            'updated_by',  
            'auth_key',
            'password_hash',
            'is_active:boolean',
        ],
    ]) ?>

</div>
