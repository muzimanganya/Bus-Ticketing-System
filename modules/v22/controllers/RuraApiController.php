<?php

namespace  app\modules\v2\controllers;

use Yii;
use yii\filters\ContentNegotiator;
use yii\web\Response;
use app\models\TenantModel; 

class RuraApiController extends \yii\rest\Controller
{
    private $connections = [];
    
    public function behaviors()
    {
        return [
            [
                'class' => ContentNegotiator::className(),
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
        ];
    }
    
    public function getDb($tenant)
    {
        $db = new yii\db\Connection([
            'dsn' => str_replace("volcano_shared", $tenant, Yii::$app->db->dsn),
            'username' => Yii::$app->db->username,
            'password' => Yii::$app->db->password,
        ]);
        
        $this->connections[] = $db;
        return $db;
    }
    
    public function afterAction($action, $result)
    {
        $result = parent::afterAction($action, $result);
        
        foreach($this->connections as $db)
        {
            $db->close();
        }
        
        return $result;
    }
}

