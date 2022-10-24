<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\models\StopSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Stops');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="stop-index">
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p class='pull-right'>
        <?= Html::a(Yii::t('app', 'Create Stop'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
<?php Pjax::begin(); ?>    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'name',
            'stopCountry',
            'created_at:date',
            [
                'attribute'=>'created_by',
                'value'=>function($model)
                {
                    return "{$model->createdBy->name}";
                }
            ],
            'updated_at:date',
            [
                'attribute'=>'updated_by',
                'value'=>function($model)
                {
                    return "{$model->updatedBy->name}";
                }
            ],

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
<?php Pjax::end(); ?></div>
