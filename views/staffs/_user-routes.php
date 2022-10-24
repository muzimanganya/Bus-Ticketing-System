<?php 
use yii\helpers\Html;
use yii\helpers\ArrayHelper;

?>
<?= Html::beginForm(['allowed-routes', 'mobile'=>$mobile], 'post', ['id'=>'ur-form']) ?>
<?= Html::checkboxList('uroutes', $selection,  ArrayHelper::map($routes, 'id', 'name')) ?>
<?= Html::endForm() ?>