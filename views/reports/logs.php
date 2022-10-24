<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\SysLogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'System Logs');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-8"></div>
        <?= Html::beginForm(['find-log-ticket'], 'post', ['class' => 'form-horizontal form-bordered']) ?>
    <div class="col-md-3">
            <?= Html::input('text', 'ticket', '', ['class' => 'form-control', 'placeholder'=>'Enter the Ticket to Search']) ?>
    </div> 
    <div class="col-md-1">
            <?= Html::submitButton('Search',['class'=>'btn btn-primary']) ?>
    </div>
        <?= Html::endForm() ?>
</div>

<div class="sys-log-index">

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?> 
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],

            //'id',
            [
                'attribute'=>'category',
                'value'=>function($model)
                {
                    return $model->category == 'BC' ? 'Move Customer' : 'Bus Capacity';
                },
                'filter'=>[
                    'BC'=>'Move Customer',
                    'CC'=> 'Bus Capacity'
                ]
            ],
            //'reference',
            'comment',
            'created_at:datetime',
            // 'created_by',
            // 'updated_at',
            // 'updated_by',

            //['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
