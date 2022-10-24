<?php

namespace app\modules\v1\controllers;
 
use yii\rest\ActiveController; 
use app\models\RouteCard;
use app\models\TenantModel;
use yii\db\Query;
use app\models\Bus;
use app\models\Route;
use app\models\Stop;
use app\models\POS;
use app\models\Customer; 
use app\models\PlannedRoute; 
use app\models\Ticket; 
use app\models\Point;

use yii\helpers\ArrayHelper;
use Yii;

use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;

/**
 * User Mgt controller for the `v1` module
 */
class PlannedRouteController extends ActiveController
{
    public $modelClass = 'app\models\PlannedRoute';
    
    public $db;
    
    public function beforeAction($action)
    {
        $return = parent::beforeAction($action);
        $this->db = TenantModel::getDb();
        return $return;
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            'authMethods' => [
                HttpBearerAuth::className(),
                QueryParamAuth::className(),
            ],
        ];

        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();

        // disable the  actions
        unset($actions['delete'], $actions['create'], $actions['index'], $actions['view']);

        return $actions;
    }

  
  public function actionPlan()
    {
       
		$success = [];

        $data = json_decode(file_get_contents('php://input'), true);
        if (is_null($data)) {
            $success['success'] = false;
            $success['message'] = 'Machine sent invalid Request';
            return $success;
        }
        
        $msg = $this->isAllowed($data['pos']);
        if (is_array($msg)) {
            return $msg;
        }

        
       //check route
        $start = $data['start'];
        $end = $data['end'];
        $time = $data['time'];
        $route = $data['route'];
    $capacity=$data['capacity'];
    $plateNo=$data['plateNo'];
 
        $originalDate = $data['date'];
        $origDateUnixTs = strtotime($originalDate.' '.str_replace('H', ':', $time));
        $date = date("Y-m-d", $origDateUnixTs);
       
 
        //get planned routes for date and hour
        $customerRoute = null;
        $bus = null;
       
        $message = "No Bus for " .Yii::$app->formatter->asDate($date, 'dd-M-yy')." ".$time ;
       
        $customerRoute = Route::findOne($route);
        if(empty($customerRoute))
        {
            $success['success'] = false;
            $success['message'] = 'Route is not valid';
            return $success;
        }
       
    $routep = Route::find()->where([
            'parent'=>null,
            'idx'=>0,
            'route'=>$customerRoute->id,
        ])->orderBy('idx')->one();
       
        if (empty($routep)) {
            $success['success'] = false;
            $success['message'] = 'No route!';
            return $success;
        }
       
        $proute = PlannedRoute::find()->where([
            'dept_date'=>$date,
            'dept_time'=>$time,
            'route'=>$customerRoute->id,
        ])->orderBy('priority ASC')->one();
         
        //no route found
        if ( !empty($proute)) {
            $success['success'] = false;
            $success['message'] = 'Bus exist!';
            return $success;
        }
       
         $bus = Bus::find()->where([
            'regno'=>$plateNo,
           
        ])->orderBy('regno')->one();
        if (empty($bus)) {
            $success['success'] = false;
            $success['message'] = 'Invalid plateNo!';
            return $success;
        }
       
        //save route ready for sending
                        $this->db->createCommand()->insert('PlannedRoute', [
                            'route' => $customerRoute->id,
                            'dept_date' => dept_date,
                            'dept_time' => dept_time,
                            'bus'=> bus,
                'priority'=>10,
                'capacity'=>capacity,
                'created_at'=>time(),                      
                            'created_by'=>Yii::$app->user->id,
                'updated_at'=>time(),
                            'updated_by'=>Yii::$app->user->id,
                'is_active'=1,
                           
                        ])->execute();
        $success['success'] = true;
            $success['message'] = 'Saved!';
            return $success;       
		
    
	}
    

 
    private function isStaff($mobile)
    {
        $sql = 'SELECT COUNT(mobile) FROM Staffs  WHERE mobile = :mobile';
        $found =  Yii::$app->db->createCommand($sql)
                        ->bindValue(':mobile', $mobile)
                        ->queryScalar();
                            
        $setting = Yii::$app->settings;
        $checkStaffs = 0;
        if ($setting->has('ticket', 'print-staff-ticket')) {
            $checkStaffs = $setting->get('ticket', 'print-staff-ticket');
        }

        return $found>0 && $checkStaffs>0 ;
    }
    
    private function isAllowed($pos)
    {
    
        $posModel = POS::findOne($pos);
        if (empty($posModel)) {
            $success['success'] = false;
            $success['message'] = 'POS Not found:';
            return $success;
        } elseif ($posModel->is_active==0) {
            $success['success'] = false;
            $success['message'] = 'POS Suspended';
            return $success;
        }
        //check if staff selling is an agent and that he is not suspended
        $staff = Yii::$app->user->identity;
        if ($staff->is_active==0) {
            $success['success'] = false;
            $success['message'] = 'You are suspended!';
            return $success;
        } elseif (!$staff->isReseller()) {
            $success['success'] = false;
            $success['message'] = 'Only Resellers can Sale!';
            return $success;
        }
        return true;
    }
    
    private function intlCustInfoMissing($customerModel)
    {
        if (
                empty($customerModel->name) ||
                empty($customerModel->passport) ||
                empty($customerModel->nationality) ||
                empty($customerModel->from_nation) ||
                empty($customerModel->to_nation) ||
                empty($customerModel->gender) ||
                empty($customerModel->age)
        ) {
            return true;
        }
        return false;
    }
}