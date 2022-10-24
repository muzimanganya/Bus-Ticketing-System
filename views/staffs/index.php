<?php

use yii\helpers\Html;
use yii\web\JsExpression;
use yii\jui\Dialog;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\models\StaffSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Staffs');
$this->params['breadcrumbs'][] = $this->title;
$this->registerCss('
    .ui-dialog-titlebar-close {
        visibility: hidden;
    }
');
?>
<div class="staff-index">
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p class='pull-right'>
        <?= Html::a(Yii::t('app', 'New Staff'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
<?php Pjax::begin(); ?>    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'mobile',
            'name',
            'location',
            'role',
            //'created_at',
            // 'created_by',
            // 'updated_at',
            // 'updated_by',
            // 'timezone',
            // 'password',
            // 'auth_key',
            // 'password_hash',
            'is_active:boolean',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update} {check-seating}',
                'buttons' => [
                    'check-seating' => function ($url, $model, $key){
                        return Html::a('<i class="fa fa-exchange " ></i>',['/staffs/allowed-routes', 'mobile'=>$model->mobile]
                        , 
                        [
                            'class'=>'user-route'
                        ]);
                    },
                ],
            ],
        ],
    ]); ?>
<?php Pjax::end(); ?></div>

<?php  
Dialog::begin([
    'id'=>'stops-dlg',
    'clientOptions' => [
        'modal' => true,
        'autoOpen' => false,
        'buttons'=> [
            [ 
                'text'=>'Save',
                'click'=>new JsExpression('function() { 
                    var dlg = this;
                    $.ajax({
                        type: "POST",
                        url: $("#ur-form").attr("action"),
                        data: $("#ur-form").serialize(),
                        success: function() {
                            $("#stops-list").html("");
                            $(dlg).dialog("close");
                        },
                        error: function() { 
                        }
                    }); 
                }')
            ],
            [ 
                'text'=>'Close',
                'click'=>new JsExpression('function() {
                    $(this).dialog("close");
                }')
            ]
        ]
    ],
]); ?>

<div id='stops-list'></div>


<?php Dialog::end(); ?>


<?php 
$this->registerJs(" 
    $('.user-route').on('click', function(e){
        var url = $(this).attr('href');
        $.get(
            url,
            function(data)
            {
                $('#stops-list').html(data);
            }
        );
        $('#stops-dlg').dialog('open');
        e.preventDefault();
    });
");
