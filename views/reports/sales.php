<?php

use yii\helpers\Html;
use yii\web\JsExpression;
use app\models\Route;
use yii\helpers\ArrayHelper;
use app\components\SumProviderRows;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\models\TicketSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Route Sales for {d}', ['d' => $date]);
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="ticket-index">
    <?php Pjax::begin(['id' => 'route']); ?>
    <div class='row'>
        <div class='col-md-9'></div>

        <div class='col-md-3'>
            <?= Html::beginForm(['sales', 'start' => $start, 'end' => $end], 'get', ['id' => 'route-form']) ?>
            <?= \yii2mod\selectize\Selectize::widget([
                'name' => 'route',
                'value' => $route,
                'items' => ArrayHelper::merge(['' => 'Select Main Route'], ArrayHelper::map(Route::find()->where(['parent' => null])->orderBy('idx ASC')->all(), 'id', function ($model) {
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
        'showFooter' => true,
        'footerRowOptions' => ['style' => 'font-weight:bold;font-size: 16px;'],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'busRoute',
                'format' => 'html',
                'value' => function ($model) use ($date) {
                    return Html::a($model->busRoute, ['bus-details', 'id' => base64_encode($date . ';' . $model->route . ';' . $model->dept_date . ';' . $model->dept_time . ';' . $model->bus)]);
                }
            ],
            'dept_date',
            'dept_time',
            'bus',
            [
                'label' => 'Capacity',
                'value' => function ($model) {
                    $capacity = is_null($model->proute) ? 0 : $model->proute->capacity;
                    $sold = $model->bookings + $model->tickets + $model->promotion + $model->staff;
                    return "$sold/$capacity";
                }
            ],
            'bookings',
            'tickets',
            'promotion',
            'staff',
            [
                'attribute' => 'RWF',
                'format' => 'integer',
                'footer' => SumProviderRows::total($dataProvider, 'RWF')
            ],
            [
                'attribute' => 'FIB',
                'format' => 'integer',
                'footer' => SumProviderRows::total($dataProvider, 'FIB')
            ],
            [
                'attribute' => 'UGS',
                'format' => 'integer',
                'footer' => SumProviderRows::total($dataProvider, 'UGS')
            ],
            [
                'attribute' => 'USD',
                'format' => 'integer',
                'footer' => SumProviderRows::total($dataProvider, 'USD')
            ],
            //['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>