<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\SysLogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Tickes for {c} ({mob}) on {d}', ['c'=>$staff->name, 'mob'=>$staff->mobile, 'd'=>$date ]);
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sys-log-index">

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?> 
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],

            'ticket',
            [
                'attribute'=>'start',
                'label'=>'Coming From',
            ],
            [
                'attribute'=>'end',
                'label'=>'Going To',
            ] ,
            [
                'attribute'=>'dept_date',
                'label'=>'Dept. Date',
            ] ,
            [
                'attribute'=>'dept_time',
                'label'=>'Time',
            ] ,
            'price',
            [
                'attribute'=>'created_by',
                'label'=>'Served By',
            ],
            [
                'attribute'=>'machine_serial',
                'label'=>'POS Machine',
            ],
            [
                'attribute'=>'updated_at',
                'format'=>'datetime',
                'label'=>'Issued On',
            ]  
        ],
    ]); ?>
</div>
