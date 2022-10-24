<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\RouteCardSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Route Cards');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="route-card-index">

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p class='pull-right'>
        <?= Html::a(Yii::t('app', 'Add Card'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'card', 
            'start',
            'end',
            'price:integer',
            'remaining_trips',
            'total_trips', 
            'owner',
	    'phone',
            [
                'attribute'=>'is_sold',
                'label'=>'Card Sold',
                'value'=>function($model)
                {
                  return $model->is_sold;  
                },
                'format'=>'boolean',
                'filter'=>[
                    0=>'Not Sold',
                    1=>'Card Sold',
                ]
            ],
	     
            // 'created_by',
            // 'updated_at',
            // 'updated_by',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
