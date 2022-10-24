<?php

use yii\helpers\Html;
use app\models\Route;
use yii\helpers\ArrayHelper;
use yii\web\JsExpression;
use app\components\SumProviderRows;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\models\TicketSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Mobile Tickets(LUMICASH) Report');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="ticket-index">
    <?php Pjax::begin(['id'=>'route']); ?>    
    <div class='row'>
        <div class='col-md-9'></div>
        
        <div class='col-md-3'>
        <?= Html::beginForm(['mobile-tickets', 'start'=>$start, 'end'=>$end], 'get', ['id'=>'route-form']) ?>
            <?= \yii2mod\selectize\Selectize::widget([
                'name'=>'route',
                'value'=>$route,
                'items'=>ArrayHelper::merge([''=>'Select Main Route'], ArrayHelper::map(Route::find()->where(['parent'=>null])->orderBy('idx ASC')->all(), 'id', function($model) {
                    return $model->start.' - '.$model->end;
                })),
                'pluginOptions' => [
                    'persist' => false,
                    'createOnBlur' => true,
                    'create' => false,
                    'onChange'=>new JsExpression('function(value)
                        {
                            $("#route-form").submit();
                        }
                    ')
                ]
            ]) ?>
        <?= Html::endForm() ?>
        </div><br />
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => null,
        'layout' => '{items}{summary}{pager}',
        'showFooter'=>false, 
        //'rowOptions'=>function($model){
            //if($model->is_staff)
                //return ['class' => 'warning']; 
            //else if($model->is_promo)
                //return ['class' => 'danger']; 
        //},
        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],

            'start',
             'end', 
            'ticket',
            'currency',
            [
                'format'=>'integer',
                'label'=>'Paid',
                'value'=>function($model)
                {
                    return $model->price - $model->discount;
                }
            ],
            'discount:integer',
            'customer',
            [
                'label'=>'Name',
                'value'=>function($model)
                {
                    return $model->customerR->name;
                }
            ],
            [
                'label'=>'Passport',
                'value'=>function($model)
                {
                    return $model->customerR->passport;
                }
            ], 
            'created_at:datetime',
            'machine_serial',
            'dept_date',
             'dept_time',
            'customer',
             'issued_on',
            // 'machine_serial',
            // 'price',
            // 'discount',
            // 'seat',
            // 'is_deleted',
            // 'is_promo',
            // 'is_staff',
             'status',
            // 'expired_in',
            // 'created_at',
            // 'created_by',
            // 'updated_at',
            // 'updated_by',

            //['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

<?php Pjax::end(); ?></div>
