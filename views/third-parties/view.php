<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\ThirdParty */

$this->title = $model->tenant;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Third Parties'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="third-party-view">

    <p>
        <?= Html::a(Yii::t('app', 'Update'), ['update', 'tenant' => $model->tenant, 'database' => $model->database], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'tenant' => $model->tenant, 'database' => $model->database], [
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
            'tenant',
            'database',
            [
                'attribute'=>'logo',
                'value'=>Yii::getAlias("{$model->logoUrl}{$model->logo}"),
                'format'=>'image'
            ]
        ],
    ]) ?>

</div>
