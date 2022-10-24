<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\WalletLogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Wallet Logs');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="wallet-log-index">

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'id',
            [
                'attribute'=>'wallet',
                'value'=>function($model)
                {
                    return "{$model->walletR->ownerR->name} {$model->walletR->currency}";
                }
            ],
            [
                'attribute'=>'ternant_db',
                'value'=>function($model)
                {
                    return $model->company->tenant;
                }
            ],
            'reference',
            [
                'attribute'=>'type',
                'value'=>function($model)
                {
                    return $model->actionType;
                }
            ],
            'previous_balance:integer',
            'current_balance:integer',
            // 'comment',
            // 'created_at',
            // 'created_by',
            // 'updated_at',
            // 'updated_by',

        ],
    ]); ?>
</div>
