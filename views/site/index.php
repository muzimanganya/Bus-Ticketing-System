<?php

use yii\helpers\Html;
use app\components\SumProviderRows;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\models\TicketSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Route Sales for {d}', ['d'=>Yii::$app->formatter->asDate($date)]);
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="ticket-index">
<p>
    <?= Html::a('Move Customer', ['move-customer'], ['class'=>'btn btn-danger pull-right']) ?>
</p>
<?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => null,
        'layout' => '{items}{summary}{pager}',
        'showFooter'=>true, 
        'footerRowOptions'=>['style'=>'font-weight:bold;font-size: 16px;'],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute'=>'busRoute',
                'format'=>'html',
                'value'=>function($model) use($date)
                {
                    return Html::a($model['name'], ['route-details', 'id'=>$model['route'], 'date'=>$date]);
                }
            ], 
            'tickets', 
            [
                'attribute'=>'RWF',
                'label'=>'RWF',
                'format'=>'integer',
                'footer'=>SumProviderRows::total($dataProvider, 'RWF')
            ],
            [
                'attribute'=>'FIB',
                'label'=>'FIB',
                'format'=>'integer',
                'footer'=>SumProviderRows::total($dataProvider, 'FIB')
            ],
            [
                'attribute'=>'UGS',
                'label'=>'UGS',
                'format'=>'integer',
                'footer'=>SumProviderRows::total($dataProvider, 'UGS')
            ] ,
            [
                'attribute'=>'USD',
                'label'=>'USD',
                'format'=>'integer',
                'footer'=>SumProviderRows::total($dataProvider, 'USD')
            ] 
        ],
    ]); ?>
</div>
