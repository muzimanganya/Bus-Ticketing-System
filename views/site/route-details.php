<?php

use yii\helpers\Html;
use app\components\SumProviderRows;
use yii2mod\selectize\Selectize;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\models\TicketSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Route Details for {d}', ['d'=>Yii::$app->formatter->asDate($date)]);
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('@web/swal/sweetalert.css');
$this->registerJsFile('@web/swal/sweetalert.min.js');

$this->registerCss("
    .popover-title {
        color: black;
        font-size: 15px;
    }
    .popover-content {
        color: black;
        font-size: 13px;
    }
    
    hr { margin: 3px 0 3px 0; }
");

?>

<div class="row">
    <div class="col-md-8"> 
        <div class='well well-sm'>
            <div class='row'>
                <div class='col-md-2'><h6>Bus: <?= $bus->regno ?></h6></div>
                <div class='col-md-2'><h6>Total Seats: <?= $bus->total_seats ?></div>
                <div class='col-md-2'><h6>Free Seats: <?= $free ?></div>
                <div class='col-md-3'><h6>Departure: <?= Yii::$app->formatter->asDate($dept_date) ?> <?= $dept_time ?></h6></div>
                <div class='col-md-3'></div>
            </div>
        </div>

        <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => null,
                'layout' => '{items}{summary}{pager}',
                'showFooter'=>true, 
                'footerRowOptions'=>['style'=>'font-weight:bold;font-size: 16px;'],
                'columns' => [
                    //['class' => 'yii\grid\SerialColumn'],
                    [
                        'label'=>'Departure - Destination', 
                        'value'=>function($model)  
                        {
                            return $model->busRoute;
                        }
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
                        'attribute'=>'UGS',
                        'format'=>'integer',
                        'footer'=>SumProviderRows::total($dataProvider, 'UGS')
                    ], 
                    [
                        'attribute'=>'USD',
                        'format'=>'integer',
                        'footer'=>SumProviderRows::total($dataProvider, 'USD')
                    ], 
                    [
                        'attribute'=>'tickets',
                        'format'=>'integer',
                        'footer'=>SumProviderRows::total($dataProvider, 'tickets')
                    ],  
                    // 'is_deleted',
                    // 'is_promo',
                    // 'is_staff',
                    // 'status',
                    // 'expired_in',
                    // 'created_at',
                    // 'created_by',
                    // 'updated_at',
                    // 'updated_by',

                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template'=>'{check-seating}', 
                        'buttons'=>[
                            'check-seating' => function ($url, $model, $key) use($bus) {
                                return Html::a('<i class="fa fa-bus" ></i>', ['get-sold-seats', 'route'=>$model->route, 'start'=>$model->start, 'end'=>$model->end, 'date'=>$model->dept_date, 'time'=>$model->dept_time, 'capacity'=>$bus->total_seats
                                ], 
                                [
                                    'class'=>'bus-loader'
                                ]);
                            },
                        ],
                    ],
                ],
            ]); ?> 
    </div>
    
    <div class="col-md-4">  
        <?= \app\widgets\Bus::widget(['model'=>$bus]) ?>
    </div>
</div>

<?php 
$this->registerJsFile('@web/js/loading-seats.js', ['depends' => [\yii\web\JqueryAsset::className()]]);

    