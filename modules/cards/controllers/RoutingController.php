<?php

namespace app\modules\cards\controllers;

use yii\data\ActiveDataProvider;
use app\models\Pricing;
use app\models\PlannedRoute;
use app\models\Route;
use app\models\Stop;
use yii\helpers\ArrayHelper;

class RoutingController extends \yii\rest\Controller
{
    public $modelClass = 'app\models\Route';
    
    public function actionStations($key)
    {
        $stops =  Stop::find()
                    ->select(['name'])
                    ->where(['LIKE', 'name', $key])
                    ->orderBy('name ASC')
                    ->limit(50)
                    ->asArray()
                    ->all();
        
        return ArrayHelper::getColumn($stops, 'name');

    }
    
    public function actionPrices($start, $end, $route)
    {
        return new ActiveDataProvider([
            'query'=>Pricing::find()->select(['price', 'currency'])->where(['start'=>$start, 'end'=>$end, 'route'=>$route])
        ]);
    }
    
    public function actionRoutes($parent=null)
    {
        $query = Route::find()
                 //->with('children')
                 ->select(['id', 'start', 'end', 'parent'])
                 ->where(['parent'=>$parent]);
                    
        return new ActiveDataProvider([
            'query'=>$query
        ]);
    }
    
    public function actionPlanned($date=null, $time=null)
    {
        $query = PlannedRoute::find()
                 ->select([
                    'route', 
                    'bus', 
                    'dept_date', 
                    'dept_time', 
                    'is_active', 
                    'capacity'
                 ])
                 ->orderBy('route ASC');
        
        if(empty($date) && empty($time))
            $query->andWhere(['dept_date'=>date('Y-m-d')]);
        
        if(!empty($date))
            $query->andWhere(['dept_date'=>$date]);
            
        if(!empty($time))
            $query->andWhere(['dept_time'=>$time]);
            
        return new ActiveDataProvider([
            'query'=>$query,
        ]);
    }
}