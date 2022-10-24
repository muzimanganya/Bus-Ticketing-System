<?php
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\web\JsExpression;
use yii\jui\Dialog;
use app\components\SumProviderRows; 

/**
 * Display Monthly reports
 *
 * @package    {package}
 * @author     hosanna
 * @copyright  2017, Hosanna HTCL
 * @license    {license}
 * @version    {version}
 * @ignore     Created by Hosanna Studio version 0.9.9, 05/10/2017
*/

$this-> title = Yii::t('app', 'Monthly Sales for {m} {y}', ['m'=>$calendar->currentMonth, 'y'=>$calendar->year]);
$this->params['breadcrumbs'][] = $this->title;

?>
<p class='pull-right'><?= Html::button('Select Month', ['class'=>'btn  btn-info', 'onclick'=>new JsExpression('
    $( "#dialog" ).dialog( "open" );
')]) ?></p> 

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'showFooter' => true,
    'footerRowOptions'=>['style'=>'font-weight:bold;'],
    'columns' => [
        [
            'attribute'=>'stops',
            'label'=>'Route',
            'format'=>'html',
            'value'=>function($model) use($calendar)
            {
                return Html::a($model['stops'], ['reports/monthly-route', 
                    'route'=>$model['route'], 
                    'month'=>$calendar->month,
                    'year'=>$calendar->year,
                ]);
            }
        ],
        [
            'attribute'=>'tickets',
            'label'=>'Total Tickets',
            'footer' => SumProviderRows::total($dataProvider, 'tickets'),
        ],
	
	
               [
            'attribute'=>'pos',
            'label'=>'POS Used',
            'footer' => SumProviderRows::total($dataProvider, 'pos'),
        ],
	
        [
            'attribute'=>'RWF',
            'label'=>'RWF',
            'format'=>'integer',
            'footer' => SumProviderRows::total($dataProvider, 'RWF'),
        ],
        [
            'attribute'=>'FIB',
            'label'=>'FIB',
             'format'=>'integer',
            'footer' => SumProviderRows::total($dataProvider, 'FIB'),
        ],
        [
            'attribute'=>'UGS',
            'label'=>'UGS',
             'format'=>'integer',
            'footer' => SumProviderRows::total($dataProvider, 'UGS'),
        ],
        [
            'attribute'=>'USD',
            'label'=>'USD',
             'format'=>'integer',
            'footer' => SumProviderRows::total($dataProvider, 'USD'),
        ],
    ],
]) ?>


<?php Dialog::begin([
    'id'=>'dialog',
    'clientOptions' => [
        'modal' => true,
        'autoOpen' => false,
    ],
]); ?>

<?php 

$form = ActiveForm::begin([
    'id' => 'login-form',
    'options' => ['class' => 'form-horizontal'],
]) ?>

    <?= $form->field($calendar, 'year')->dropDownList($calendar->getYears()) ?>
    <?= $form->field($calendar, 'month')->dropDownList($calendar->getMonths()) ?>
    
    <div class="form-group">
        <div class="col-lg-offset-1 col-lg-11">
            <?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
        </div>
    </div>
<?php ActiveForm::end() ?>

<?php Dialog::end(); ?>




