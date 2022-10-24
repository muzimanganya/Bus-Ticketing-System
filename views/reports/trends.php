<?php
/* @var $this yii\web\View */
$this->title = 'Charts and Trends';
use sjaakp\gcharts\AreaChart;
use sjaakp\gcharts\PieChart;
?>

<?= AreaChart::widget([
    'height' => '400px',
    'dataProvider' => $weekly,
    'columns' => [
        'day:string',
        'RWF',
        'FIB',
        'UGS',
        'USD'
    ],
    'options' => [
        'title' => 'Weekly Sales Trend'
    ],
]) ?><br />

<?= PieChart::widget([
    'height' => '400px',
    'dataProvider' => $bestSellers,
    'columns' => [
        'author:string',
        'RWF',
        'FIB',
        'UGS',
        'USD',
    ],
    'options' => [
        'title' => 'Monthly 5 Best Sellers',
        'is3D'=>true,
    ],
]) ?><br />

<?= PieChart::widget([
    'height' => '400px',
    'dataProvider' => $bestRoutes,
    'columns' => [
        'broute:string',
        'RWF',
        'FIB',
        'UGS',
        'USD',
    ],
    'options' => [
        'title' => 'Monthly 5 Best Routes',
        'is3D'=>true,
    ],
]) ?>