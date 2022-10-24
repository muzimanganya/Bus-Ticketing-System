<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\WalletLog */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Wallet Logs'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="wallet-log-view">

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'wallet',
            'ternant_db',
            'reference',
            'actionType',
            'previous_balance',
            'current_balance',
            'comment',
            'created_at',
            'created_by',
            'updated_at',
            'updated_by',
        ],
    ]) ?>

</div>
