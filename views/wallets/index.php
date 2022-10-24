<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\WalletSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Wallets');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="wallet-index">

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p class='pull-right'>
        <?= Html::a(Yii::t('app', 'Topup'), ['topup'], ['class' => 'btn btn-danger']) ?>
        <?= Html::a(Yii::t('app', 'New Wallet'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'id',
            'currency',
            [
                'attribute'=>'owner',
                'value'=>function($model)
                {
                    return $model->ownerR->name;
                }
            ],
            'current_amount:integer',
            'last_recharged:integer',
            // 'created_at',
            // 'created_by',
            // 'updated_at',
            // 'updated_by',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
