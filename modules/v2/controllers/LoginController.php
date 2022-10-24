<?php

namespace app\modules\v2\controllers;

use Yii;
use app\models\Vendor;
use yii\data\ActiveDataProvider;
use yii\web\HttpException; 
use yii\filters\ContentNegotiator;
use yii\web\Response;
/**
 * 
 */
class VendorController extends \yii\rest\Controller
{

    public function init()
    {
        Yii::$app->response->formatters[\yii\web\Response::FORMAT_JSON] = 'app\modules\v2\helpers\RuraApiFormatter';

        parent::init();
    }
    
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

	public function actionVender($name=null, $date=null)
   	{
        if (empty($name) || empty($date))
            return [['status_code'=>500, 'companies'=>'', 'error'=>'Missing parameter']];

    	$query =Yii::$app->db->createCommand('SELECT * FROM TenantMapping WHERE company LIKE :name AND approval_date LIKE :approval_date')
    			->bindValues(['name' => "%{$name}%",'approval_date' => "%{$date}%"])
    			->queryAll();

        if (empty($query)) 
            return [['status_code'=>400, 'companies'=>'', 'error'=>'No companies found']];
               
        $companies =[]; 
        foreach ($query as $key => $value) {
        	$companies["{$key} "]= ['company' => $value['rura_name'], 'date' => $value['starting_date']];
		
        }      
        //return ['status'=>200, 'data'=>$companies, 'fieldName'=>'companies'];
	return ['status'=>200, 'companies'=>$companies]; 
    }
}
