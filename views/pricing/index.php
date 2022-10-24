<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\grid\GridView;
use app\models\Route;

/* @var $this yii\web\View */
/* @var $searchModel app\models\PricingSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Pricing');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pricing-index">

    <p class='pull-right'>
        <?= Html::a(Yii::t('app', 'New Pricing'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'start',
            'end',
            [
                'attribute'=>'route',
                'value'=>function($model)
                {
                    return "{$model->routeR->start}-{$model->routeR->end}";
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
            'price:integer',
            'currency',
            // 'created_at',
            // 'created_by',
            // 'updated_at',
            // 'updated_by',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
