<?php 

use yii\grid\GridView;
use yii\widgets\Pjax;

?>

<?php Pjax::begin(['id'=>'type_id']); //id is used for jquery opertaion  ?> 

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'layout'=>"{items}\n{pager}",
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        'created_at:datetime',
        'comment'
    ],
]) ?>
<?php Pjax::end(); ?>