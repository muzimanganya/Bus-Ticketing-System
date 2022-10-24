<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\grid\GridView;

use app\models\User;
use app\modules\mobile\models\Company;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\mobile\models\CompanyLogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Wallet Logs');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="company-log-index">


    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],

            'id',
            [
                'attribute'=>'company',
                'value'=>function($model)
                {
                    return $model->companyR->name;
                },
                'filter'=>\yii2mod\selectize\Selectize::widget([
                    'attribute'=>'company',
                    'model'=>$searchModel,
                    'items'=>ArrayHelper::map(Company::find()->all(), 'id', function($model) {
                        return  $model->name;
                    })
                ])
            ],
            [
                'attribute'=>'type',
                'value'=>function($model)
                {
                    return $model->typeStr;
                },
                'filter'=>[
                    'TO'=>'Top up only',
                    'TI'=>'Ticket Selling'
                ]
            ],
            'reference',
            'amount:integer',
            'change:integer',
            'comment',
            [
                'attribute'=>'created_at',
                'value'=>function($model)
                {
                    return Yii::$app->formatter->asDatetime($model->created_at, 'short');
                }
            ],
            [
                'attribute'=>'created_by',
                'value'=>function($model)
                {
                    return $model->createdBy->name;
                },
                'filter'=>\yii2mod\selectize\Selectize::widget([
                    'attribute'=>'created_by',
                    'model'=>$searchModel,
                    'items'=>ArrayHelper::map(User::find()->where(['role'=>'root'])->all(), 'id', function($model) {
                        return  $model->name;
                    })
                ])
            ],
            // 'updated_at',
            // 'updated_by',
            // 'ternant_db',

            /*[
                'class' => 'yii\grid\ActionColumn',
                'template'=>'{view}'
            ],*/
        ],
    ]); ?>
</div>
