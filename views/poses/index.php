<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\models\POSSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'POS Machines');
$this->params['breadcrumbs'][] = $this->title;
$this->registerJs('
    function itemClicked(id, checked)
    {
        if(checked)
            checked = 1;
        else
            checked = 0;
        $.post({
            url:"'.Url::to(['/poses/mark']).'",
            data:{"checked":checked, "id":id},
            dataType:"json",
            success:function(data)
            {
                if(data.success)
                {
                    $.pjax.reload({container:"#gridview"});
                }
            }
        });
    }
', \yii\web\View::POS_END);
?>
<div class="pos-index">
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(Yii::t('app', 'Add Machine'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

<?php Pjax::begin(); ?>   
 <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'id'=>'gridview', 
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'serial',
            'mobile',
            'simcard', 
            'location', 
            //'created_at',
            // 'created_by',
            // 'updated_at',
            // 'updated_by',
            'is_active:boolean',
            [
                'class' => 'yii\grid\CheckboxColumn', 
                'header'=>'',
                'checkboxOptions' => function($model, $key, $index, $widget) {
                    return [
                        "value" => $model->serial,
                        'checked'=>$model->is_active == 1,
                        'onclick' =>'js:itemClicked(this.value, this.checked)'
                    ];
                },
            ], 

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
<?php Pjax::end(); ?></div>
