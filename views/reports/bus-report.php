<?php

use yii\helpers\Html; 
use yii\web\JsExpression;
use yii\helpers\ArrayHelper; 
use app\components\SumProviderRows; 
use yii\grid\GridView; 
use app\models\Bus;
/* @var $this yii\web\View */
/* @var $searchModel app\models\TicketSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Bus Sales for {b} {d}', ['b'=>$bus, 'd'=>$date]);
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="ticket-index">
<div class='row'>
        <div class='col-md-9'></div>
        
        <div class='col-md-3'>
        <?= Html::beginForm(['bus-report', 'start'=>$start, 'end'=>$end], 'get', ['id'=>'bus-form']) ?>
            <?= \yii2mod\selectize\Selectize::widget([
                'name'=>'reference',
                'value'=>$bus,
                'items'=>ArrayHelper::merge([''=>'Select Bus'], ArrayHelper::map(Bus::find()->all(), 'regno', function($model) {
                    return $model->regno;
                })),
                'pluginOptions' => [
                    'persist' => false,
                    'createOnBlur' => true,
                    'create' => false,
                    'onChange'=>new JsExpression('function(value)
                        {
                            $("#bus-form").submit();
                        }
                    ')
                ]
            ]) ?>
        <?= Html::endForm() ?>
        </div><br />
    </div>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => null,
            'layout' => '{items}{summary}{pager}',
            'showFooter'=>true, 
            'footerRowOptions'=>['style'=>'font-weight:bold;font-size: 16px;'],
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'], 
                'bus',
                [
                    'attribute'=>'tickets',
                    'format'=>'integer',
                    'label'=>'Tickets',
                    'label'=>'Tickets Sold',
		     'footer'=>SumProviderRows::total($dataProvider, 'tickets')

                ],
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
                    'attribute'=>'USD',
                    'label'=>'USD',
                    'format'=>'integer',
                    'footer'=>SumProviderRows::total($dataProvider, 'USD')
                ], 
                //['class' => 'yii\grid\ActionColumn'],
            ],
        ]); ?>
</div>
