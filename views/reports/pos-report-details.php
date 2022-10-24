<?php

use yii\helpers\Html; 
use app\components\SumProviderRows;
use yii\helpers\ArrayHelper; 
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\models\TicketSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'POS Sales Details for {p}', ['p'=>$pos]);
$this->params['breadcrumbs'][] = ["label" => "POS Sales", "url" => ["/reports/pos-sales", 'date'=>$date]];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="ticket-index">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => null,
            'layout' => '{items}{summary}{pager}',
            'showFooter'=>true, 
            'footerRowOptions'=>['style'=>'font-weight:bold;font-size: 16px;'],
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'], 
                [
                    'attribute'=>'author', 
                    'label'=>'Cashier Name'
                ],
                'tickets',
                [
                    'attribute'=>'RWF',
                    'format'=>'integer',
                    'label'=>'RWF',
                    'footer'=>SumProviderRows::total($dataProvider, 'RWF')
                ],
                [
                    'attribute'=>'FIB',
                    'format'=>'integer',
                    'label'=>'FIB',
                    'footer'=>SumProviderRows::total($dataProvider, 'FIB')
                ],
                [
                    'attribute'=>'UGS',
                    'format'=>'integer',
                    'label'=>'UGS',
                    'footer'=>SumProviderRows::total($dataProvider, 'UGS')
                ],
                [
                    'attribute'=>'USD',
                    'format'=>'integer',
                    'label'=>'USD',
                    'footer'=>SumProviderRows::total($dataProvider, 'USD')
                ]
                //['class' => 'yii\grid\ActionColumn'],
            ],
        ]); ?>
</div>
