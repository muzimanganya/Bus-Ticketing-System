<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\models\BusSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Buses');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bus-index">
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p class='pull-right'>
        <?= Html::a(Yii::t('app', 'Add Bus'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
<?php Pjax::begin(); ?>    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'regno',
            //'leftseats',
            //'rightseats',
            //'backseats',
            //'doorside',
            [
                'attribute'=>'driver',
                'value'=>function($model)
                {
                    return empty($model->driverR) ? null : "{$model->driverR->name} - {$model->driver}";
                }
            ],
             'total_seats',
            'created_at', 
            [
                'attribute'=>'created_by',
                'value'=>function($model)
                {
                    return "{$model->createdBy->name}";
                }
            ],
            // 'updated_at',
            // 'updated_by',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
<?php Pjax::end(); ?></div>
