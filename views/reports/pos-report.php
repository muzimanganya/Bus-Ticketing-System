<?php

use yii\helpers\Html; 
use app\components\SumProviderRows;
use yii\helpers\ArrayHelper; 
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\models\TicketSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'POS Sales for {d}', ['d'=>$date]);
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
                    'label'=>'POS Machine',
                    'format'=>'html',
                    'value'=>function($model) use($start, $end)
                    {
                        return Html::a($model->machine_serial, ['pos-sales-details', 'start'=>$start, 'end'=>$end, 'pos'=>$model->machine_serial]);
                    }
                ],
                [
                    'label'=>'POS Location',
                    'value'=>function($model)
                    {
                        return $this->context->db
                                    ->createCommand('SELECT location FROM POSes WHERE serial=:id', ['id'=>$model->machine_serial])
                                    ->queryScalar();
                    }
                ],
                [
                    'attribute'=>'tickets',
                    'label'=>'Tickets Sold'
                ],
                [
                    'attribute'=>'RWF',
                    'format'=>'integer',
                    'footer'=>SumProviderRows::total($dataProvider, 'RWF')
                ],
                [
                    'attribute'=>'FIB',
                    'format'=>'integer',
                    'footer'=>SumProviderRows::total($dataProvider, 'FIB')
                ],
                [
                    'attribute'=>'USD',
                    'format'=>'integer',
                    'footer'=>SumProviderRows::total($dataProvider, 'USD')
                ], 
                //['class' => 'yii\grid\ActionColumn'],
            ],
        ]); ?>
</div>
