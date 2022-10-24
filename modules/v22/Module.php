<?php

namespace app\modules\v2;
use Yii;
/**
 * v2 module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'app\modules\v2\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
            parent::init();
            $this->registerComponents();
            Yii::$app->user->enableSession = false;
            Yii::$app->user->loginUrl = null;
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON ;
    }

    public function registerComponents(){
        \Yii::$app->setComponents([
            'response' => [
            'class' => 'yii\web\Response',
           /*  'on beforeSend' => function ($event) {
                $response = $event->sender;
                if ($response->data !== null && $response->statusCode !=200 ) {                    
                    $response->data = [
                      'status_code' => 600,
                       'error' => 'ticketing system',
                       \Yii::error($response->content, __METHOD__), 
                        ];               
                    }
                } */
            ]  
        ]);     
    }

    public function afterAction($action, $result)
    {
        $result = parent::afterAction($action, $result);
        
        if(Yii::$container->has('db_tenant'))  
        {
            $db = Yii::$container->get('db_tenant');
            if(!empty($db))
                $db->close();
        }
        
        return $result;
   }
}
