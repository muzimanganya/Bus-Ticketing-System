<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;
use app\models\Route;
use app\models\Stop;
/* @var $this yii\web\View */
/* @var $searchModel app\models\TicketSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Deleted Tickets');
$this->params['breadcrumbs'][] = $this->title;

$this->registerJs('
    $("button").on("click", function(e){
        var keys = $("#grid").yiiGridView("getSelectedRows");
        $.post({
           url: "'.Url::to(['/tickets/undelete']).'", 
           dataType: "json",
           data: {keys: keys},
           success: function(data) {
              alert(data.message);
              if (data.success) {
                  window.location.reload();
              }
                 
           },
        });

        
        e.preventDefault();
    });
', \yii\web\View::POS_END);
?>

<p class='pull-right'>
    <?= Html::button(Yii::t('app', 'Restore Selected'), ['class' => 'btn btn-danger']) ?>
</p>

<?php Pjax::begin(); ?>    
    <?= GridView::widget([
        'id'=>'grid',
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],
            'ticket',
            [
                'attribute'=>'route',
                'value'=>function($model)
                {
                    return is_null($model->routeR) ? 'Parent Route' : "{$model->routeR->name} ({$model->routeR->start} - {$model->routeR->end})";
                },
                'filter'=>\yii2mod\selectize\Selectize::widget([
                    'model'=>$searchModel,
                    'attribute'=>'route',
                    'items'=> ArrayHelper::merge([''=>'All Routes'], ArrayHelper::map(Route::find()->where(['parent'=>null])->all(), 'id', function($model) {
                            return "{$model->name} ({$model->start} - {$model->end})";
                        }
                    )), 
                    'pluginOptions' => [ 
                        // define list of plugins 
                        'persist' => false,
                        'createOnBlur' => false,
                        'allowEmptyOption' => true
                    ]
                ])
            ],
            //'bus',
            [
                'attribute'=>'start',
                'filter'=>\yii2mod\selectize\Selectize::widget([
                    'model'=>$searchModel,
                    'attribute'=>'start',
                    'items'=>ArrayHelper::merge([''=>'All Departure'], ArrayHelper::map(Stop::find()->all(), 'name', function($model) {
                            return $model->name;
                        })
                    ), 
                    'pluginOptions' => [ 
                        // define list of plugins 
                        'plugins' => ['drag_drop', 'remove_button'],
                        'persist' => false,
                        'createOnBlur' => true,
                        'create' => true
                    ]
                ])
            ], 
            [
                'attribute'=>'end',
                'filter'=>\yii2mod\selectize\Selectize::widget([
                    'model'=>$searchModel,
                    'attribute'=>'end',
                    'items'=>ArrayHelper::merge([''=>'All Destination'], ArrayHelper::map(Stop::find()->all(), 'name', function($model) {
                            return $model->name;
                        })
                    ), 
                    'pluginOptions' => [
                        'persist' => false,
                        'createOnBlur' => true,
                        'create' => true
                    ]
                ])
            ],
            [
                'attribute'=>'dept_date',
                'format'=>'date', 
                'filter'=>\yii\jui\DatePicker::widget([
                    'model'  => $searchModel,
                    'attribute'  => 'dept_date', 
                    'dateFormat' => 'yyyy-MM-dd',
                    'options' => ['class' => 'form-control']
                ])
            ] ,
            [
                'attribute'=>'dept_time', 
                'filter'=>\yii\widgets\MaskedInput::widget([
                    'model'  => $searchModel,
                    'attribute'  => 'dept_time', 
                    'mask' => '99H99', 
                ])
            ] , 
            // 'customer',
            // 'issued_on',
            // 'machine_serial',
            'price',
            'currency',
            // 'discount',
            // 'seat',
            // 'is_deleted',
            // 'is_promo',
            // 'is_staff',
            // 'status',
            // 'expired_in',
            // 'created_at',
            // 'created_by',
            // 'updated_at',
            // 'updated_by',
            // 'mobile_money',
            
            [
                'class' => 'yii\grid\CheckboxColumn', 
                //'header'=>'Select',
                /*'checkboxOptions' => function($model, $key, $index, $widget) {
                    return [
                        "value" => json_encode($model->getPrimaryKey()),
                        'checked'=>false, //$model->is_active == 1,
                        //'onclick' =>'js:itemClicked(this.value, this.checked)'
                    ];
                },*/
            ], 
            //['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
<?php Pjax::end(); ?></div>
