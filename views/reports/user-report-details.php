<?php

use yii\helpers\Html; 
use app\components\SumProviderRows;
use yii\helpers\ArrayHelper; 
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\models\TicketSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'User Sales Details for {n}', ['n'=>$name]);
$this->params['breadcrumbs'][] = ["label" => "User Sales", "url" => ["/reports/user-sales", 'date'=>$date]];
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
                    'attribute'=>'pos', 
                    'label'=>'POS Machine'
                ],
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
                    'attribute'=>'USD',
                    'format'=>'integer',
                    'label'=>'USD',
                    'footer'=>SumProviderRows::total($dataProvider, 'USD')
                ]
                //['class' => 'yii\grid\ActionColumn'],
            ],
        ]); ?>
</div>
