<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\ArrayHelper;  
use app\models\Route; 
use app\models\Bus;

/* @var $this yii\web\View */
/* @var $searchModel app\models\PlannedRouteSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Planned Routes');
$this->params['breadcrumbs'][] = $this->title;
$this->registerJs('
    function itemClicked(id, checked)
    {
        if(checked)
            checked = 1;
        else
            checked = 0;
        $.post({
            url:"'.Url::to(['/planned-routes/mark']).'",
            data:{"checked":checked, "id":id},
            dataType:"json",
            success:function(data)
            {
                if(data.success)
                { 
                    alert(data.msg);
                    location.reload(); 
                }
                else
                {
                    alert(data.msg);
                }
            }
        });
    }
', \yii\web\View::POS_END);
?>
<div class="planned-route-index">
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p class='pull-right'>
        <?= Html::a(Yii::t('app', 'Plan a Route'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
<?php Pjax::begin(); ?>    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute'=>'route',
                'value'=>function($model)
                {
                    return "{$model->routeR->start} - {$model->routeR->end}";
                },
                'filter'=>\yii2mod\selectize\Selectize::widget([
                    'model'=>$searchModel,
                    'attribute'=>'route',
                    'items'=>ArrayHelper::merge([''=>'Choose Route   '], ArrayHelper::map(Route::find()->where(['parent'=>null])->all(), 'id', function($model) {
                            return $model->start.' - '.$model->end;
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
                'attribute'=>'bus',
                'filter'=>\yii2mod\selectize\Selectize::widget([
                    'model'=>$searchModel,
                    'attribute'=>'bus',
                    'items'=>ArrayHelper::merge([''=>'Choose Bus   '],ArrayHelper::map(Bus::find()->all(), 'regno', function($model) {
                            return $model->regno;
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
            'capacity',
            'priority',
            [
                'class' => 'yii\grid\CheckboxColumn', 
                'header'=>'',
                'checkboxOptions' => function($model, $key, $index, $widget) {
                    return [
                        "value" => json_encode($model->getPrimaryKey()),
                        'checked'=>$model->is_active == 1,
                        'onclick' =>'js:itemClicked(this.value, this.checked)'
                    ];
                },
            ], 
            [
                'class' => 'yii\grid\ActionColumn',
                'template'=>'{view}{update}'
            ],
        ],
    ]); ?>
<?php Pjax::end(); ?></div>
