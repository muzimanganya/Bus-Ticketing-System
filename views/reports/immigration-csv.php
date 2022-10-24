<?php

use yii\helpers\Html;
use app\models\Ticket;
use yii\web\JsExpression;
use yii\helpers\ArrayHelper;
use app\components\SumProviderRows;
use DivineOmega\Countries\Countries;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\models\TicketSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Immigration Report');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="ticket-index">
    <div class='well well-sm'>
        <div class='row'>
            <?php foreach ($dataProvider->getModels() as $model) : ?>
                <?php if (!empty($model->proute)) : ?>
                    <div class='col-md-2'>
                        <h6>Bus: <?= $model->bus ?></h6>
                    </div>
                    <div class='col-md-4'>
                        <h6>Depart: <?= Yii::$app->formatter->asDate($model->dept_date) ?> at <?= $model->dept_time ?></h6>
                    </div>
                    <div class='col-md-2'>
                        <h6>All Seats: <?= $model->proute->capacity ?>
                    </div>

                    <div class="col-md-4"></div>
                    <?php break; ?>
                <?php endif; ?>
                <?php continue; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <?= Html::a('<i class="fa fa-download"></i> Export Data', $downloadURL, ['class' => 'btn btn-primary pull-right', 'target' => '_blank']) ?>
    <div class="clearfix"></div>

    <div class="table-responsive">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => null,
            'layout' => '{items}{summary}{pager}',
            'showFooter' => false,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                [
                    'label' => 'First Name',
                    'value' => function ($model) {
                        if (empty($model->customerR)) return null;

                        $name = explode(' ', $model->customerR->name);
                        if (count($name) > 0) return $name[0];
                        return null;
                    }
                ],
                [
                    'label' => 'Surname',
                    'value' => function ($model) {
                        if (empty($model->customerR)) return null;

                        $name = explode(' ', $model->customerR->name);
                        if (count($name) > 1) return $name[1];
                        return null;
                    }
                ],
                [
                    'label' => 'Other Name(s)',
                    'value' => function ($model) {
                        if (empty($model->customerR)) return null;

                        $name = explode(' ', $model->customerR->name);
                        if (count($name) > 2) return $name[2];
                        return null;
                    }
                ],
                'ticket',
                [
                    'label' => 'Nationality',
                    'value' => function ($model) {
                        if (empty($model->customerR)) return null;

                        return $model->customerR->nationality;
                    }
                ],
                [
                    'label' => 'Gender',
                    'value' => function ($model) {
                        if (empty($model->customerR)) return null;

                        return $model->customerR->genderName;
                    }
                ],
                [
                    'label' => 'Date of Birth',
                    'format' => 'date',
                    'value' => function ($model) {
                        if (empty($model->customerR)) return null;

                        return $model->customerR->dob;
                    }
                ],
                [
                    'label' => 'Traveller Type',
                    'value' => function($model){ 
                        return $model->travellerType;
                    }
                ],
                [
                    'label' => 'Document Type',
                    'value' => function($model){ 
                        return $model->documentType;
                    }
                ],
                [
                    'label' => 'Document Country',
                    'value' => function($model){ 
                        $countries = new Countries();
                        return $model->doc_country == null ? null : $countries->getByIsoCode($model->doc_country)->name ;
                    }
                ],
                'doc_number',
                'doc_expiry:date',
                [
                    'attribute' => 'from_country',
                    'value' => function($model){ 
                        $countries = new Countries();
                        return $model->from_country == null ? null : $countries->getByIsoCode($model->from_country)->name ;
                    }
                ],
                [
                    'label' => 'Port of Embark',
                    'value' => function($model){ 
                        return $model->start  ;
                    }
                ],
                [
                    'attribute' => 'to_country',
                    'value' => function($model){ 
                        $countries = new Countries();
                        return $model->to_country == null ? null : $countries->getByIsoCode($model->to_country)->name ;
                    }
                ],
                [
                    'label' => 'Port of Disembark',
                    'value' => function($model){ 
                        return $model->end  ;
                    }
                ],
                'seat',
                'number_of_bags',
                'bag_tags'
                //['class' => 'yii\grid\ActionColumn'],
            ],
        ]); ?>
    </div>
</div>