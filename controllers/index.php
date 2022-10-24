<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\models\TicketSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Manage Tickets');
$this->params['breadcrumbs'][] = $this->title;

$this->registerJs('
    $("button").on("click", function(e){
        var keys = $("#grid").yiiGridView("getSelectedRows");
        $.post({
           url: "'.Url::to(['/tickets/delete']).'", 
           dataType: "json",
           data: {keys: keys},
           success: function(data) {
              alert(data.message);
              if (data.success) {
                  window.location.reload();
              }
                 
           },
        });

        
        e.preventDefault();
    });
', \yii\web\View::POS_END);
?>

<p class='pull-right'>
    <?= Html::button(Yii::t('app', 'Delete Selected'), ['class' => 'btn btn-danger']) ?>
</p>

<?php Pjax::begin(); ?>    
    <?= GridView::widget([
        'id'=>'grid',
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],
            'ticket',
            //'route',
            //'bus',
            'start',
            'end',
            'dept_date',
            'dept_time',
            // 'customer',
            // 'issued_on',
            // 'machine_serial',
            'price',
            'currency',
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
            // 'mobile_money',
            
            [
                'class' => 'yii\grid\CheckboxColumn', 
                'header'=>'Select',
                /*'checkboxOptions' => function($model, $key, $index, $widget) {
                    return [
                        "value" => json_encode($model->getPrimaryKey()),
                        'checked'=>false, //$model->is_active == 1,
                        //'onclick' =>'js:itemClicked(this.value, this.checked)'
                    ];
                },*/
            ], 
            //['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
<?php Pjax::end(); ?></div>
