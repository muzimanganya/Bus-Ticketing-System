<?php

use yii\helpers\Html;
use app\models\Ticket;
use yii\web\JsExpression;
use app\models\Route;
use yii\helpers\ArrayHelper;
use app\components\SumProviderRows;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\models\TicketSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Route Bus Details');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="ticket-index">
    <div class='well well-sm'>
        <div class='row'>
            <?php foreach ($dataProvider->getModels() as $model) : ?>
                <?php if (!empty($model->proute)) : ?>
                    <div class='col-md-1'>
                        <h6><?= $model->bus ?></h6>
                    </div>
                    <div class='col-md-2'>
                        <h6>Depart: <?= Yii::$app->formatter->asDate($model->dept_date) ?> at <?= $model->dept_time ?></h6>
                    </div>
                    <div class='col-md-1'>
                        <h6>All Seats: <?= $model->proute->capacity ?>
                    </div>
                    <div class='col-md-1'>
                        <h6>Sold: <?= $dataProvider->getTotalCount() ?>
                    </div>
                    <div class='col-md-3'>
                        <h6>Revenue: <?= SumProviderRows::totalCurrency($dataProvider) ?></h6>
                    </div>
                    <div class='col-md-4'><?= Html::a($model->routeR->start . ' - ' . $model->routeR->end, ['sales', 'route' => $model->route, 'date' => $date], ['class' => 'btn btn-default btn-block']) ?></div>
                    <?php break; ?>
                <?php endif; ?>
                <?php continue; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <?php Pjax::begin(); ?>
    <div class='row'>
        <div class="col-md-3"><?= Html::a(Yii::t('app', 'Immigration Report'), $immigrationURL, ['class' => 'btn btn-success btn-md']) ?></div>
        <div class='col-md-6'></div>

        <div class='col-md-3'>
            <?= Html::beginForm(['bus-details', 'id' => $_GET['id']], 'get', ['id' => 'route-form']) ?>
            <?= \yii2mod\selectize\Selectize::widget([
                'name' => 'startEnd',
                'value' => $startEnd,
                'items' => ArrayHelper::merge(['' => 'Select Route'], ArrayHelper::map(Ticket::find()
                    ->select(['start', 'end'])
                    ->where(['route' => $route])
                    ->andWhere(['BETWEEN', 'dept_date', $dept_date, date('Y-m-d')])
                    ->groupBy('start,end')
                    ->orderBy('start, end')->all(), 'startEnd', function ($model) {
                    return $model->start . ' - ' . $model->end;
                })),
                'pluginOptions' => [
                    'persist' => false,
                    'createOnBlur' => true,
                    'create' => false,
                    'onChange' => new JsExpression('function(value)
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
        'showFooter' => false,
        'rowOptions' => function ($model) {
            if ($model->is_staff)
                return ['class' => 'warning'];
            else if ($model->is_promo)
                return ['class' => 'danger'];
        },
        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],

            'start',
            'end',
            'ticket',
            'currency',
            [
                'format' => 'integer',
                'label' => 'Paid',
                'value' => function ($model) {
                    return $model->price - $model->discount;
                }
            ],
            'discount:integer',
            'customer',
            [
                'label' => 'Name',
                'value' => function ($model) {
                    return empty($model->customerR) ? 'UNKNOWN' : $model->customerR->name;
                }
            ],
            [
                'label' => 'Passport',
                'value' => function ($model) {
                    return null;// empty($model->customerR) ? 'UNKNOWN' : $model->customerR->passport;
                }
            ],
            'seat',
            [
                'attribute' => 'updated_at',
                'label' => 'Sold at',
                'format' => 'datetime',
            ],
            'machine_serial',
            // 'dept_date',
            // 'dept_time',
            // 'customer',
            // 'issued_on',
            // 'machine_serial',
            // 'price',
            // 'discount',
            // 'seat',
            // 'is_deleted',
            // 'is_promo',
            // 'is_staff',
            // 'status',
            // 'expired_in',
            // 'created_at',
            // 'created_by',
            // 'updated_at',
            // 'updated_by',

            //['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>