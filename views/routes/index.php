<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\ArrayHelper; 
use app\models\Stop;
use app\models\Route;
/* @var $this yii\web\View */
/* @var $searchModel app\models\RouteSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */


$this->title = Yii::t('app', 'Routes');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="route-index">
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p class='pull-right'>
        <?= Html::a(Yii::t('app', 'New Route'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
<?php Pjax::begin(); ?>    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            //'id',
            [
                'attribute'=>'start',
                'filter'=>\yii2mod\selectize\Selectize::widget([
                    'model'=>$searchModel,
                    'attribute'=>'start',
                    'items'=>ArrayHelper::merge([''=>'Choose Stop   '], ArrayHelper::map(Stop::find()->all(), 'name', function($model) {
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
                    'items'=>ArrayHelper::merge([''=>' Choose Stop   '], ArrayHelper::map(Stop::find()->all(), 'name', function($model) {
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
                'attribute'=>'parent',
                'value'=>function($model)
                {
                    return is_null($model->parentR) ? 'Parent Route' : "{$model->parentR->name} ({$model->parentR->start} - {$model->parentR->end})";
                },
                'filter'=>\yii2mod\selectize\Selectize::widget([
                    'model'=>$searchModel,
                    'attribute'=>'parent',
                    'items'=> ArrayHelper::merge([''=>'All Parents'], ArrayHelper::map(Route::find()->where(['parent'=>null])->all(), 'id', function($model) {
                            return "{$model->name} ({$model->start} - {$model->end})";
                        }
                    )), 
                    'pluginOptions' => [ 
                        // define list of plugins 
                        'plugins' => ['drag_drop', 'remove_button'],
                        'persist' => false,
                        'createOnBlur' => true,
                        'allowEmptyOption' => true
                    ]
                ])
            ],  
            [
                'attribute'=>'is_intl',
                'label'=>'Is International',
                'format'=>'boolean',
                'filter'=>[0=>'Local Route', 1=>'International']
            ], 
            [
                'attribute'=>'has_promotion',
                'format'=>'boolean',
                'filter'=>[0=>'No Promo', 1=>'Has Promo']
            ],  
            [
                'attribute'=>'send_sms',
                'format'=>'boolean',
                'filter'=>[0=>'No SMS', 1=>'Send SMS']
            ], 

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
<?php Pjax::end(); ?></div>
