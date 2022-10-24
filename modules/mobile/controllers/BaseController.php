<?php

namespace app\modules\mobile\controllers;

use yii\web\Controller;
use yii\filters\AccessControl;
use Yii;

/**
 * Default controller for the `mobile` module
 */
class BaseController extends Controller
{
    public function beforeAction($action)
    {
        $this->layout = '@app/modules/mobile/views/layouts/main';
        return parent::beforeAction($action);
    }
    
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'except'=>['login', 'error'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
                'denyCallback' => function ($rule, $action) {
                    return Yii::$app->response->redirect(['/mobile/default/login']);
                },
            ],
        ];
    }
}
