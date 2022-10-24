<?php

use yii\helpers\Html;
use app\models\Route;
use yii\helpers\ArrayHelper;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\models\PlanTemplateSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Plan Templates');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="plan-template-index">

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p class='pull-right'>
        <?= Html::a(Yii::t('app', 'New to Plan'), ['create'], ['class' => 'btn btn-success']) ?>
        <?= Html::a(Yii::t('app', 'Generate Plan'), ['plan'], ['class' => 'btn btn-danger']) ?>
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
            'bus',
            'hour',
            'pcapacity',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
<?php Pjax::end(); ?></div>
