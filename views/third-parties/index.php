<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\mobile\models\ThirdPartySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Third Parties');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="third-party-index">


    <p class="pull-right">
        <?= Html::a(Yii::t('app', 'New Company'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'tenant',
            'database',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
