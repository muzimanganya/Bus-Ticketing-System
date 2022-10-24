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

$this->registerJs('
    $("a").on("click", function(event){
        var data = JSON.parse($(this).find("i").attr("id"));
        var url = $(this).find("i").attr("data-url");
        var type = $(this).find("i").attr("data-type");

        if(type=="logs")
        {
            //show logs
            //post thru Ajax
            $.post(url,{"data":data}, function( data ) {
                if(data.success)
                {
                    $("#contents").html(data.message);
                    //show dialog 
                    $( "#logs-dlg" ).dialog("open"); 
                }
                else
                {
                    alert("Failed to fetch Logs. Try again!");
                }
            });
        }
        else
        {
            $.post(url,{"data":data}, function( result ) {
                $("#capacity-contents").html(result.message);
                $("#capacity-contents").attr("data-url", url);
                //set PK 
                $("#capacitychangeform-pk").val(JSON.stringify(data));
                $("#capacitychangeform-capacity").val(data["capacity"]);
                $( "#capacity-dlg" ).dialog("open"); 
            });
        }
    });
');

 ?>

<div class="row">
    <div class="col-md-8"> 
        <!--div class='well well-sm'>
            <div class='row'>
                <div class='col-md-2'><h6>Bus: < ? = $bus->regno ?></h6></div>
                <div class='col-md-2'><h6>Total Seats: < ? = $bus->total_seats ?></div>
                <div class='col-md-2'><h6>Free Seats: < ? = $free ?></div>
                <div class='col-md-3'><h6>Departure: < ? = Yii::$app->formatter->asDate($dept_date) ?> < ? = $dept_time ?></h6></div>
                <div class='col-md-3'></div>
            </div>
        </div-->

        <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => null,
                'layout' => '{items}{summary}{pager}',
                'showFooter'=>true, 
                'footerRowOptions'=>['style'=>'font-weight:bold;font-size: 16px;'],
                'columns' => [
                    //['class' => 'yii\grid\SerialColumn'],
                    [
                        'label'=>'Route', 
                        'format'=>'html', 
                        'value'=>function($model) use($date) 
                        {
							return Html::a("{$model['start']}-{$model['end']}", ['/reports/bus-details', 'id'=>base64_encode($date.';'.$model['route'].';'.$model['dept_date'].';'.$model['dept_time'])]);
                        }
                    ], 
                    'dept_date', 
                    'dept_time',  
					[
						'attribute'=>'tickets',  
						'value'=>function($model)
						{
							return "{$model['tickets']}/{$model['capacity']}";
						}
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
					],
                    [
                       'class' => \yii\grid\ActionColumn::className(),
                       'template'=>'{change-route-capacity}{show-route-logs}',
                        'buttons'=>[
                            'change-route-capacity' => function ($url, $model, $key) {
                                $url = substr($url, 0, strpos($url, "?"));

                                $id = ['route'=>$model['route'], 'dept_date'=>$model['dept_date'], 'dept_time'=>$model['dept_time'], 'bus'=>$model['bus'], 'capacity'=>$model['capacity']];
                                $id = json_encode($id);
                                return  Html::a("<i class='glyphicon glyphicon-pencil' id='{$id}' data-type='capacity' data-url={$url}></i>", false,['style'=>'padding:5px;']) ;
                            },

                            'show-route-logs' => function ($url, $model, $key) {
                                $url = substr($url, 0, strpos($url, "?"));

                                $id = ['route'=>$model['route'], 'dept_date'=>$model['dept_date'], 'dept_time'=>$model['dept_time'], 'bus'=>$model['bus']];
                                $id = json_encode($id);
                                return  Html::a("<i class='glyphicon glyphicon-list-alt' id='{$id}' data-type='logs' data-url={$url}></i>", false) ;
                            },
                        ],
                    ],
                ],
            ]); ?> 
    </div>
    
    <!--div class="col-md-4">  
        < ?= \app\widgets\Bus::widget(['model'=>$bus]) ?>
    </div-->
</div>

<?php 
$this->registerJsFile('@web/js/loading-seats.js', ['depends' => [\yii\web\JqueryAsset::className()]]);

    
\yii\jui\Dialog::begin([
    'id'=>'logs-dlg',
    'clientOptions' => [
        'modal' => true,
        'autoOpen' => false,
        'height'=>'400',
        'width'=>'600',
        'title'=>'Planned Route Change Log',
        'buttons'=>[
            [
                'text'=>'Close',
                'click'=>new \yii\web\JsExpression('function(){ $( this ).dialog( "close" ); }'),
                'class'=>'btn btn-danger'
            ]
        ]
    ],
]);

echo '<div id="contents"></div>'; 

\yii\jui\Dialog::end();


    
\yii\jui\Dialog::begin([
    'id'=>'capacity-dlg',
    'clientOptions' => [
        'modal' => true,
        'autoOpen' => false,
        'height'=>'300',
        'width'=>'400',
        'title'=>'Change Bus Capacity',
        'buttons'=>[
            [
                'text'=>'Submit',
                'click'=>new \yii\web\JsExpression('function(){ 
                    var url = $("#capacity-contents").attr("data-url");
                    var data = $("#capacity-contents").find("form").serialize();
                    $.post(url, data , function( result ) {
                        if(result.success)
                        {
                            location.reload(); 
                        }
                        else if(result.message == null)
                        {
                            alert("Unknown Error!");
                        }
                        else
                        {
                            $("#capacity-contents").html(result.message);
                        }
                    });

                 }'),
                'class'=>'btn btn-primary'
            ],
            [
                'text'=>'Close',
                'click'=>new \yii\web\JsExpression('function(){ $( this ).dialog( "close" ); }'),
                'class'=>'btn btn-danger'
            ]
        ]
    ],
]);

echo '<div id="capacity-contents"></div>'; 

\yii\jui\Dialog::end();

