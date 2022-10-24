<?php

namespace app\modules\v1;
use Yii;
/**
 * v1 module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\modules\v1\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        
        Yii::$app->setComponents([
            'request' => [
                'class'=>'yii\web\Request',
                'enableCsrfValidation'=>false,
                'enableCookieValidation'=>false,
                'parsers' => [
                    'application/json' => 'yii\web\JsonParser',
                ]
            ],
            'response' => [
                'class'=>'yii\web\Response',
                'format' =>  \yii\web\Response::FORMAT_JSON,
                'formatters' => [
                    \yii\web\Response::FORMAT_JSON => [
                        'class' => 'yii\web\JsonResponseFormatter',
                        'prettyPrint' => YII_DEBUG, // use "pretty" output in debug mode
                        //'encodeOptions' => JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
                    ],
                    \yii\web\Response::FORMAT_XML => [
                        'class' => 'yii\web\JsonResponseFormatter',
                        'prettyPrint' => YII_DEBUG, // use "pretty" output in debug mode
                        //'encodeOptions' => JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
                    ],
                ],
            ],
        ]);
        
        Yii::$app->user->enableSession = false;
        Yii::$app->user->loginUrl = null;
        //bind to this event
        Yii::$app->response->on(\yii\web\Response::EVENT_BEFORE_SEND, function ($event) 
        {
            $response = $event->sender;
            if ($response->data !== null && $response->statusCode !== 200) 
            {
                $response->data = [
                    'success' => $response->isSuccessful,
                    'message' => $response->data['message'],
                ];
                ;
            }
        });
    }
}
