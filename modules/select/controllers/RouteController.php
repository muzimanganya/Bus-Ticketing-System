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
        
        
	 $success['message'] = 'Hello! Ticket:{$start}, {$end},{ $time} ,{$route}, {$capacity},{$plateNo},{$originalDate}.Thanks';
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