<?php

use yii\helpers\Html;
use app\models\Route;
use app\models\PlannedRoute;
use yii\helpers\ArrayHelper;
use yii\web\JsExpression;
use app\components\SumProviderRows;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\models\TicketSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Bookings Report {d} {t}', ['d'=>$date, 't'=>$time]);
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="ticket-index">
    <?php Pjax::begin(['id'=>'route']); ?>    
    <div class='row'>
        <div class='col-md-8'></div>
        
        <div class='col-md-4'>
        <?= Html::beginForm(['booking', 'date'=>$date], 'get', ['id'=>'route-form']) ?>
            <div class='row'>
				<div class='col-md-8'>
					<?= \yii2mod\selectize\Selectize::widget([
                        'name'=>'route',
                        'value'=>$route,
                        'items'=>ArrayHelper::merge([''=>'Select Main Route'], ArrayHelper::map(Route::find()->where(['parent'=>null])->orderBy('idx ASC')->all(), 'id', function ($model) {
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
				</div>
				<div class='col-md-4'>
					<?= \yii2mod\selectize\Selectize::widget([
                        'name'=>'time',
                        'value'=>$time,
                        'items'=>ArrayHelper::merge([''=>'Select Hour'], ArrayHelper::map(PlannedRoute::find()->where(['route'=>$route])->all(), 'dept_time', function ($model) {
                            return $model->dept_time;
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
				</div>
			</div>
        <?= Html::endForm() ?>
        </div><br />
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => null,
        'layout' => '{items}{summary}{pager}',
        'showFooter'=>false,
        'rowOptions'=>function ($model) {
            if ($model->is_staff) {
                return ['class' => 'warning'];
            } elseif ($model->is_promo) {
                return ['class' => 'danger'];
            }
        },
        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],
            'bus',
            'start',
             'end',
            [
                'label'=>'Booking No.',
                'value'=>function ($model) {
                    return $model->ticket;
                }
            ],
            'currency',
            'price:integer',
            'customer',
            [
                'label'=>'Name',
                'value'=>function ($model) {
                    return $model->customerR->name;
                }
            ],
            'seat',
            [
                'attribute'=>'issued_on',
                'value'=>function ($model) {
                    $date = new \DateTime('now', new \DateTimeZone(Yii::$app->user->identity->timezone));
                    $date->setTimestamp($model->issued_on);
                    return $date->format('d M H:i'); 
                }
            ],
            [
                'label'=>'Expires',
                'value'=>function ($model) {
                    $dateStr = $model->dept_date.' '.str_replace('H', ':', $model->dept_time);
                    $date = new \DateTime($dateStr, new \DateTimeZone(Yii::$app->timeZone));
                    $date->modify("-{$model->expired_in} second");
                    /*echo '<pre>';
                    var_dump($date);
                    echo '</pre>';
                    die();*/
                    $date->setTimeZone(new \DateTimeZone(Yii::$app->user->identity->timezone));
                    return $date->format('d M H:i'); 
                }
            ],
            'machine_serial',
            // 'dept_date',
            // 'dept_time',
            // 'customer',
            // 'issued_on',
            // 'machine_serial',
            // 'price',
            // 'discount',
            // 'is_deleted',
            // 'is_promo',
            // 'is_staff',
            // 'status',
            //'expired_in',
            // 'created_at',
            // 'created_by',
            // 'updated_at',
            // 'updated_by',

            //['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

<?php Pjax::end(); ?></div>
