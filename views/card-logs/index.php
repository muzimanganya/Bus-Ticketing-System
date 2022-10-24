<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\CardLogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Card Logs');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="card-log-index">
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],

            'id',
            'card',
            'created_at:datetime',
            [
				'attribute'=>'created_by',
				'value'=>function($model)
				{
					return "{$model->createdBy->name} - {$model->created_by}";
				}
            ],
            //'updated_at',
            //'updated_by',
            'remained_trips',

            //['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
