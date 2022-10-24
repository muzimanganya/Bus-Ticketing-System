<?php

use yii\helpers\Html;
use yii\web\JsExpression;
use app\models\Staff;
use yii\helpers\ArrayHelper;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\RouteCardSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Card Sales');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="route-card-index">
  <div class='row'>
        <div class='col-md-8'></div>
        
        <div class='col-md-4'>
        <?= Html::beginForm(['/reports/card-sales', 'start'=>$start,'end'=>$end], 'get', ['id'=>'user-cards-form']) ?>
            <?= \yii2mod\selectize\Selectize::widget([
                'name'=>'reference',
                'value'=>$mobile,
                'items'=>ArrayHelper::merge([''=>'Select Cashier'], ArrayHelper::map(Staff::find()->where(['role'=>'reseller', 'company'=>Yii::$app->user->identity->company])->orderBy('name ASC')->all(), 'mobile', function($model) {
                    return "{$model->name} - {$model->mobile}";
                })),
                'pluginOptions' => [
                    'persist' => false,
                    'createOnBlur' => true,
                    'create' => false,
                    'onChange'=>new JsExpression('function(value)
                        {
                            $("#user-cards-form").submit();
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
        'showFooter'=>true,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'card', 
            'owner',
	    'phone', 
            [
                'attribute'=>'start',
                'label'=>'Route',
                'value'=>function($model)
                {
                  return "{$model->start}-{$model->end}";  
                }, 
            ],
	    
            'price:integer',
            'remaining_trips',
            'total_trips', 
            [
                'attribute'=>'created_by',
                'label'=>'Sold By',
                'value'=>function($model)
                {
                  return $model->soldBy->name;  
                }, 
            ],
	    'pos',
            [
                'label'=>'Value',
                'value'=>function($model)
                {
                    $total = number_format($model->price*$model->total_trips);
                    return "$total {$model->currency}" ;  
                },
                'footer'=>$total
            ], 
        ],
    ]); ?>
</div>
