<?php

namespace app\modules\cards;
use Yii;
/**
 * v1 module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\modules\cards\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        //for now test VRwanda Only
        Yii::$app->params['tenant_db'] = 'stella_db';

        Yii::$app->user->enableSession = false;
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    }
}
