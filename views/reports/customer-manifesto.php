<?php 

use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\helpers\Html; 


$this->title = 'Travellers Manifesto';

$gen = Yii::$app->formatter->asDateTime(time());

$pdfHeader = [
  'L' => [
    'content' => "Bus: {$model->bus} Leaving {$model->dept_date} {$model->dept_time} {$route->start}-{$route->end}",
  ],
  'C' => [
    'content' => '',
  ],
  'R' => [
    'content' => 'VOLCANO MANIFESTO REPORT',
    'font-size' => 10,
    'font-style' => 'B',
    'font-family' => 'arial',
    'color' => '#333333'
  ],
  'line' => true,
];

$pdfFooter = [
  'L' => [
    'content' => "Generated at $gen",
    'font-size' => 10,
    'color' => '#333333',
    'font-family' => 'arial',
  ],
  'C' => [
    'content' =>'', //'CENTER CONTENT (FOOTER)',
  ],
  'R' => [
    'content' =>'', // 'RIGHT CONTENT (FOOTER)' ,
    'font-size' => 10,
    'color' => '#333333',
    'font-family' => 'arial',
  ],
  'line' => true,
];

?> 
<?=  GridView::widget([
    'dataProvider' => $dataProvider,     
    'exportConfig' => [
        GridView::PDF => [
            'filename' => 'Volcano_Manifesto_'.$model->dept_time.'_'.$model->dept_date,
            'config' => [
              'methods' => [
                'SetHeader' => [
                  ['odd' => $pdfHeader, 'even' => $pdfHeader]
                ],
                'SetFooter' => [
                  ['odd' => $pdfFooter, 'even' => $pdfFooter]
                ],
              ],
              'options' => [
                'title' => 'Volcano Manifesto',
                'subject' => 'Bus Boarding Report',
                'keywords' => 'pdf, preceptors, export, other, keywords, here'
              ],
            ]
        ],
    ],

    'export' => [
        'fontAwesome'=>false,
        'showConfirmAlert'=>false,
        'target'=>GridView::TARGET_BLANK
    ],
    'showPageSummary'=>true,
    'panel'=>[
        'type'=>'default',
        'heading'=>'User Boarded in the Bus'
    ],
    'autoXlFormat'=>true, 
    'columns'=>[
        'name',
        'mobile',
        'nationality',
	'gender',

        'ticket',
        'seat',
        [
          'attribute'=>'passport',
          'label'=>'Passport or ID',
        ],
        [
          'attribute'=>'end',
          'label'=>'Destination',
        ],
        
    ]
]) ?>