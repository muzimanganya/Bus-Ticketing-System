<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView; 
use yii\helpers\ArrayHelper;  
use app\models\Route; 
use app\models\Bus;
/* @var $this yii\web\View */
/* @var $searchModel app\models\BoardingTimeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Boarding Times');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="boarding-time-index">

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p class='pull-right'>
        <?= Html::a(Yii::t('app', 'Add Time'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],

            [
                'attribute'=>'route',
                'value'=>function($model)
                {
                    return $model->routeR->routeName;
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
            'start',
            'end',
            'offset',
            //'created_at',
            // 'created_by',
            // 'updated_at',
            // 'updated_by',

            [
				'class' => 'yii\grid\ActionColumn',
				'template'=> '{update}{delete}'
            ],
        ],
    ]); ?>
</div>
