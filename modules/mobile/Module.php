<?php

namespace app\modules\mobile;

use Yii;

/**
 * mobile module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\modules\mobile\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        Yii::$app->set('user', [
            'class' => 'yii\web\User',
            'identityClass' => 'app\modules\mobile\models\User',
            'enableAutoLogin' => true,
            'loginUrl' => ['/mobile/default/login'],
        ]);
    }
    
    public function beforeAction($action)
    {
        if ($action->id == 'login') {
            return true;
        }
        
        /*if(Yii::$app->user->identity instanceof \app\modules\mobile\models\User)
        {
            return true;
        }
        else
        {
            //return false;
        }*/
        
        return parent::beforeAction($action);
    }
}
