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
class TripController extends ActiveController
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
            $success['success'] = true;
            $success['message'] = 'Route is not valid';
            return $success;
        }
       
    $routep = Route::find()->where([
            'parent'=>null,
            'idx'=>0,
            'start'=>$start,
'end'=>$end,
        ])->orderBy('idx')->one();
       
        if (empty($routep)) {
            $success['success'] = false;
            $success['message'] = "Route {$start}-{$end} is not valid!";
            return $success;
        }
       
        $proute = PlannedRoute::find()->where([
            'dept_date'=>$date,
            'dept_time'=>$time,
            'route'=>$routep->id,
        ])->orderBy('priority ASC')->one();
         
        //no route found
        if ( !empty($proute)) {
            $success['success'] = false;
            $success['message'] = "This trip {$start}-{$end} for {$date},{$time} has already added before.Try another please!";
            return $success;
        }
       
         $bus = Bus::find()->where([
            'regno'=>$plateNo,
           
        ])->orderBy('regno')->one();
        if (empty($bus)) {
            $success['success'] = false;
            $success['message'] = "Invalid plateNo {$plateNo}.Try again!";
            return $success;
        }
 	if($bus->total_seats==0)
	{
            $success['success'] = false;
            $success['message'] = "Invalid plateNo {$plateNo}.Try again!";
            return $success;
        }

       
        //save route ready for sending
                        $this->db->createCommand()->insert('PlannedRoutes', [
                            'route' => $routep->id,
                            'dept_date' => $date,
                            'dept_time' => $time,
                            'bus'=> $plateNo,
               		    'priority'=>10,
                            'capacity'=>$bus->total_seats,
                            'created_at'=>time(),                      
                            'created_by'=>Yii::$app->user->id,
                            'updated_at'=>time(),
                            'updated_by'=>Yii::$app->user->id,
                            'is_active'=>1,
                           
                        ])->execute();
        $success['success'] = true;
            $success['message'] = "Trip {$start}-{$end} for {$date},{$time} with bus {$plateNo} added successfully !\n\n\n";
            return $success;

       
		
    
	}
    
 public function actionManifest()//shows bus details report per route
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

        $date = $data['date'];
        
        $date = $data['date'];
        $origDateUnixTs = strtotime($date.' '.str_replace('H', ':', $time));
        $date = date("Y-m-d", $origDateUnixTs);
	
	   
	$dbman=null;
	$settings = Yii::$app->settings;
	if ($settings->has('ticket', 'nomanifest')) {
            if ($settings->get('ticket', 'nomanifest')==1) {
               
		$dbman=1;
            }
        }       
        if($dbman==1)
	{
		 $success['success'] = false;
            $success['message'] = 'No manifest allowed!';
            return $success;
	}


        //get planned routes for date and hour
        $customerRoute = null;
        $bus = null;
        
        $message = "No Bus for " .Yii::$app->formatter->asDate($date, 'dd-M-yy')." ".$time ;
        
        $customerRoute = Route::findOne($route);
        
        if (!$customerRoute->hasRoute($start, $end)) {
            //check if it is return reoute
            if ($customerRoute->returnR && $customerRoute->returnR->hasRoute($start, $end)) {
                //the stops suggests this is a return route not a go route
                $customerRoute = $customerRoute->returnR;
            } else {
                $success['success'] = false;
                $success['message'] = $message;
                return $success;
            }
        }
        
        $proute = PlannedRoute::find()->where([
            'dept_date'=>$date,
            'dept_time'=>$time,
            'route'=>$customerRoute->id,
        ])->orderBy('priority ASC')->one();
         
        //no route found
        if (empty($proute)) {
            $success['success'] = false;
            $success['message'] = 'No Bus for that Time!';
            return $success;
        }

        $bus = Bus::findOne($proute->bus);
        
        //$customersSQL = 'SELECT CONCAT(c.name,",",r.abrevroute) as customer  FROM Routes r,Tickets t ,Customers c  WHERE c.mobile = t.customer and (r.parent=t.route OR r.parent is null) and r.start=t.start and r.end=t.end and and t.status="CO" AND t.dept_date=:dept_date AND dept_time=:dept_time AND route=:route AND bus = :bus ';
       $customersSQL = 'SELECT CONCAT(start ,"-", end,":",count(id)) as customer  FROM Tickets t  WHERE t.dept_date=:dept_date AND  t.status="CO" AND  is_deleted=0 AND dept_time=:dept_time AND route=:route AND bus = :bus group by concat(start,"-",end)';
	$customers = $this->db->createCommand($customersSQL)
            ->bindValue(':dept_date',  $proute->dept_date)
            ->bindValue(':dept_time',  $proute->dept_time)
            ->bindValue(':route',  $proute->route)
            ->bindValue(':bus',  $proute->bus)
            ->queryColumn();
            
        $revenueSQL = 'SELECT CONCAT(currency, " - ", SUM(price-discount),",Total Tickets:",COUNT(id)) as revenue,updated_by as cashier FROM Tickets WHERE is_deleted=0  AND t.status="CO" AND dept_date=:dept_date AND dept_time=:dept_time AND route=:route AND bus = :bus GROUP BY currency';
        $revenue = $this->db->createCommand($revenueSQL)
            ->bindValue(':dept_date',  $proute->dept_date)
            ->bindValue(':dept_time',  $proute->dept_time)
            ->bindValue(':route',  $proute->route)
            ->bindValue(':bus',  $proute->bus)
            ->queryColumn();
       // $revenue.="Cashier ";
             // $revenue.=Yii::$app->user->id;        
        $success['success'] = true;
        $success['message'] = [
            'bus'=>$bus->regno,
            'dept_date'=>$data['date'],
            'dept_time'=>$time,
            'route'=>$customerRoute->name,
            'customers'=>$customers,
            'revenue'=>$revenue,
        ];
        return $success;
    }

public function actionCapacity()
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
    	//$plateNo=$data['plateNo'];
 
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
            $success['success'] = true;
            $success['message'] = 'Route is not valid';
            return $success;
        }
       
    $routep = Route::find()->where([
            'parent'=>null,
            'idx'=>0,
            'start'=>$start,
            'end'=>$end,
        ])->orderBy('idx')->one();
       
        if (empty($routep)) {
            $success['success'] = false;
            $success['message'] = "Route {$start}-{$end} is not valid!";
            return $success;
        }
       
        $proute = PlannedRoute::find()->where([
            'dept_date'=>$date,
            'dept_time'=>$time,
            'route'=>$routep->id,
        ])->orderBy('priority ASC')->one();
         
        //no route found
        if ( empty($proute)) {
            $success['success'] = false;
            $success['message'] = "This trip {$start}-{$end} for {$date},{$time} Not exist in system";
            return $success;
        }
       
        $proute->capacity=$proute->capacity+$capacity;
		$proute->updated_at = time();
		$proute->updated_by=Yii::$app->user->id;
		$proute->save(false);
       
                $success['success'] = true;
            $success['message'] = "{$capacity} seats added on {$start}-{$end} for {$date},{$time} with bus {$proute->bus} successfully. Total capacity is {$proute->capacity}!\n\n\n";
            return $success;

       
		
    
	}




public function actionPrint()
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
        $pos = $data['pos'];
         
       
       
    $pticket = Ticket::find()->where([
            'machine_serial'=>$pos,
            'updated_by'=>Yii::$app->user->id,
            
        ])->orderBy('id DESC')->one();
       
        if (empty($pticket)) {
            $success['success'] = false;
            $success['message'] = 'Ticket is not valid!';
            return $success;
        }
       
              
        $pticket->is_deleted=1;
	$pticket->status='RE';
	$pticket->is_promo=-1;
		$pticket->updated_at = time();
		$pticket->updated_by=Yii::$app->user->id;
		$pticket->save(false);
	//$z= $this->db->createCommand('insert into DeletedTickets  select * from Tickets  WHERE is_deleted=1')
	   
	





	 //  ->execute();
	 $pticket = Ticket::find()->where([
            'machine_serial'=>$pos,
            'updated_by'=>Yii::$app->user->id,
	    'is_deleted'=>1,
            'status'=>'RE',
            
        ])->orderBy('id DESC')->one();
       
        if (empty($pticket)) {
            $success['success'] = false;
            $success['message'] = 'Ticket is not valid!';
            return $success;
        }


	$this->db->createCommand('INSERT DeletedTickets SELECT * FROM Tickets WHERE id=:id')
                    ->bindValue(':id', $pticket->id)
                    ->execute();


	//Ticket::deleteAll(['id'=>$pticket->id]);
                       // $success['success'] = false;


                $success['success'] = true;
            $success['message'] = 'Done';
            return $success;

       
		
    
	}



     public function actionSellCards()//check nearest available card no to sell
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
		
        $status=0;
	
	 $cards = RouteCard::find()->where([
            'is_sold'=>$status,
            
        ])->orderBy('card ASC')->one(); 
	 
	 if (empty($cards)) {
            $success['success'] = false;
            $success['message'] = "No cards available.Please contact the system adminstrator!";
            return $success;
        }

	
           
                $success['success'] = false;
                $success['message'] = "Koresha numero ya telephone y'umugenzi. Gukata tickets uzajya ukuraho zero ibanza (78..).";
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