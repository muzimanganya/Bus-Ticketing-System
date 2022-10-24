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
 
<?php Pjax::begin(); ?>    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => null,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute'=>'route',
                'value'=>function($model)
                {
                    return "{$model->routeR->start} - {$model->routeR->end}";
                } 
            ], 
            [
                'attribute'=>'bus', 
            ],
            [
                'attribute'=>'dept_date',
                'format'=>'date' 
            ] ,
            [
                'attribute'=>'dept_time',  
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
        ],
    ]); ?>
<?php Pjax::end(); ?></div>
