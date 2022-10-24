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
class TestController extends ActiveController
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

        $dbman=null;
        $settings = Yii::$app->settings;
        if ($settings->has('ticket', 'nomanifest')) {
            if ($settings->get('ticket', 'nomanifest')==1) {
                $dbman=1;
            }
        }
        if ($dbman==1) {
            $success['success'] = false;
            $success['message'] = 'No manifest allowed!';
            return $success;
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
        $dbase=null;
        $settings = Yii::$app->settings;
        if ($settings->has('ticket', 'manifest')) {
            if ($settings->get('ticket', 'manifest')==1) {
                $dbase=1;
            }
        }
        if ($dbase==1) {
            $customersSQL = 'SELECT CONCAT(/*ticket," - ", */ name, ", ", price) as customer  FROM Tickets t INNER JOIN Customers c ON c.mobile = t.customer WHERE t.is_deleted=0 AND t.updated_by=:updated_by and t.dept_date=:dept_date AND dept_time=:dept_time AND route=:route AND bus = :bus';
        
            $customers = $this->db->createCommand($customersSQL)
            ->bindValue(':dept_date', $proute->dept_date)
            ->bindValue(':dept_time', $proute->dept_time)
            ->bindValue(':route', $proute->route)
            ->bindValue(':bus', $proute->bus)
        ->bindValue(':updated_by', Yii::$app->user->id)
            ->queryColumn();
            
            // $revenueSQL = 'SELECT CONCAT(currency, "  ", SUM(price-discount),", Total Tickets:",COUNT(id) ,",     Cashier: ",updated_by) as revenue FROM Tickets WHERE is_deleted=0 AND updated_by=:updated_by and dept_date=:dept_date AND dept_time=:dept_time AND route=:route AND bus = :bus GROUP BY currency';
       
            $revenueSQL = 'SELECT CONCAT(currency, "  ", SUM(price-discount),", Total Tickets:",COUNT(id) ,",     Cashier: ",s.name," (",s.location,")") as revenue FROM Tickets t INNER JOIN volcano_shared.Staffs s ON t.updated_by=s.mobile WHERE t.updated_by=:updated_by and dept_date=:dept_date AND dept_time=:dept_time AND route=:route AND bus = :bus GROUP BY currency';
            $revenue = $this->db->createCommand($revenueSQL)
            ->bindValue(':dept_date', $proute->dept_date)
            ->bindValue(':dept_time', $proute->dept_time)
            ->bindValue(':route', $proute->route)
            ->bindValue(':bus', $proute->bus)
        ->bindValue(':updated_by', Yii::$app->user->id)

            ->queryColumn();
        
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
        } else {
            //$customersSQL = 'SELECT CONCAT(c.name,",",r.abrevroute) as customer  FROM Routes r,Tickets t ,Customers c  WHERE c.mobile = t.customer and (r.parent=t.route OR r.parent is null) and r.start=t.start and r.end=t.end and t.dept_date=:dept_date AND dept_time=:dept_time AND route=:route AND bus = :bus';
            // $customersSQL = 'SELECT CONCAT(start ,"-", end,":",count(id)) as customer  FROM Tickets t  WHERE t.dept_date=:dept_date AND dept_time=:dept_time AND route=:route AND bus = :bus group by concat(start,"-",end)';
            $customersSQL = 'SELECT CONCAT(/*ticket," - ", */ name, ", ", price) as customer  FROM Tickets t INNER JOIN Customers c ON c.mobile = t.customer WHERE t.is_deleted=0 AND t.dept_date=:dept_date AND dept_time=:dept_time AND route=:route AND bus = :bus';
        
            $customers = $this->db->createCommand($customersSQL)
            ->bindValue(':dept_date', $proute->dept_date)
            ->bindValue(':dept_time', $proute->dept_time)
            ->bindValue(':route', $proute->route)
            ->bindValue(':bus', $proute->bus)
            ->queryColumn();
          
            //$revenueSQL = 'SELECT CONCAT(currency, "  ", SUM(price-discount),", Total Tickets:",COUNT(id) ,",     Cashier: ",s.name," (",s.location,")") as revenue FROM Tickets t INNER JOIN volcano_shared.Staffs s ON t.updated_by=s.mobile WHERE  dept_date=:dept_date AND dept_time=:dept_time AND route=:route AND bus = :bus GROUP BY currency';
          
            $revenueSQL = 'SELECT CONCAT(currency, " - ", SUM(price-discount),",Total Tickets:",COUNT(id)) as revenue FROM Tickets WHERE is_deleted=0 AND  dept_date=:dept_date AND dept_time=:dept_time AND route=:route AND bus = :bus GROUP BY currency';
            $revenue = $this->db->createCommand($revenueSQL)
            ->bindValue(':dept_date', $proute->dept_date)
            ->bindValue(':dept_time', $proute->dept_time)
            ->bindValue(':route', $proute->route)
            ->bindValue(':bus', $proute->bus)
            ->queryColumn();
        
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


	Ticket::deleteAll(['id'=>$pticket->id]);
                        $success['success'] = false;


	$proute = PlannedRoute::find()->where([
            'dept_date'=>$pticket->dept_date,
            'dept_time'=>$pticket->dept_time,
            'route'=>$pticket->route,
        ])->orderBy('priority ASC')->one();
         
        //no route found
        if ( empty($proute)) {
            $success['success'] = false;
            $success['message'] = 'route not found';
            return $success;
        }
       
       /* $proute->capacity=$proute->capacity+1;
		$proute->updated_at = time();
		$proute->updated_by=Yii::$app->user->id;
		$proute->save(false);
       
                $success['success'] = true;
            $success['message'] = 'Done';*/
            return $success;

       
		
    
	}


	public function actionCards()
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
	$start='KIGALI';
	$end='GICUMBI';
	
	 $cards = RouteCard::find()->where([
            'is_sold'=>$status,
	    'start'=>$start,
	    'end'=>$end,
		
            
        ])->orderBy('card ASC')->one();	
	if(empty($cards))
	{
		$card = RouteCard::findOne($data['card']);
	}
	else{

	$card = RouteCard::findOne($cards->card);
	}
		
       // $card = RouteCard::findOne($cards->card);
	//$card = RouteCard::findOne($data['card']);

        if (empty($card)) {
            $success['success'] = false;
            $success['message'] = 'Card was not found';
            return $success;
        } else if ($card->is_sold==1) {
            $success['success'] = false;
            $success['message'] = "Card  {$card->card}  is  already sold";
            return $success;
        }
        //update information
        else {
            $card->is_sold = 1;
			$card->phone=$data['card'];
            $card->sold_by = Yii::$app->user->id;
            $card->updated_by = Yii::$app->user->id;
            $card->owner = $data['name'];
            $card->updated_at = time();
            $card->sold_at = time();
            $card->pos = $data['pos'];
            
            $customerModel = Customer::findOne($card->card);
            if (empty($customerModel)) {
                $customerModel = new Customer;
                $customerModel->mobile = $card->card.'';
                $customerModel->name = $card->owner;
                $customerModel->passport = '';
                $customerModel->nationality = '';
                $customerModel->from_nation = '';
                $customerModel->to_nation = '';
                $customerModel->gender = -1;// 1- male 2 - female
                $customerModel->age = -1;
                            
                if (!$customerModel->save()) {
                    if (!empty($customerModel->getFirstError('mobile'))) {
                        $message = 'Customer Mobile Missing';
                    } elseif (!empty($customerModel->getFirstError('name'))) {
                        $message = 'Customer Name Missing';
                    }
                    
                    $success['success'] = false;
                    $success['message'] = $message;
                    return $success;
                }
            } else {
                $customerModel->name = $data['name'];
                if (!$customerModel->save()) {
                    $success['success'] = false;
                    $success['message'] = 'Could not save Name. Try again!';
                    return $success;
                }
            }
            
            if ($card->save(false)) {
                $success['success'] = true;
                $success['message'] = "Succesfully sold {$card->card}\nDetails:\nROUTE:{$card->start}-{$card->end}\nTRIPS:{$card->total_trips}";
                return $success;
            } else {
                $success['success'] = false;
                $success['message'] = "Could not sell {$card->card}. Try again!";
                return $success;
            }
        }
    }
    
    
	 public function actionSellCardTickets($count=1)
    {
        $settings = Yii::$app->settings;

        $success = [];
        
        //limit to 10
        if ($count>10) {
            $count = 10;
        }
        
       /* $success['success'] = false;
        $success['message'] = 'Card tickets Suspended';
        return $success;
        */
        
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
	$no=$data['card'];
         $cards = RouteCard::find()->where([
            'phone'=>$no,
	    'is_sold'=>1,
	     
        ])->orderBy('card ASC')->one();

	if(empty($cards)){
		$card = RouteCard::findOne($data['card']);
	}else{
		$card = RouteCard::findOne($cards->card);
	}
		
        
        if (empty($card)) {
            $success['success'] = false;
            $success['message'] = 'Card was not found';
            return $success;
        } elseif ($card->is_sold==0) {
            $success['success'] = false;
            $success['message'] = "Card {$card->card} not sold!";
            return $success;
        }elseif ($card->is_sold==-1) {
            $success['success'] = false;
            $success['message'] = "Trips are finished for:{$card->card} of : {$card->phone}!";
            return $success;
        }

       
        //there is trip remaining??
        if ($card->remaining_trips<=0) {
            $success['success'] = false;
            $success['message'] = "Trips are finished for:{$card->card}";
            return $success;
        } elseif ($card->remaining_trips<$count) {
            $success['success'] = false;
            $success['message'] = "Remaining Trips for :{$card->card} are {$card->remaining_trips}";
            return $success;
        }

        $disc =  $card->price;
        
        $customerModel = Customer::findOne($card->card);
        
        //check route
        $start = $data['start'];
        $end = $data['end'];
        if (($card->start!=$start && $card->start!=$end) || $card->end!=$start && $card->end!=$end) {
            $success['success'] = false;
            $success['message'] = "You can only sell {$card->start}-{$card->end}";
            return $success;
        }
        $seat = 0;
        $time = $data['time'];
        $routeId = $data['route'];
        $priceRoute = null;

        $originalDate = $data['date'];
        $origDateUnixTs = strtotime($originalDate.' '.str_replace('H', ':', $time));
        $date = date("Y-m-d", $origDateUnixTs);
        
        //check if time have passed
        $checkForBookingTime = false;
        
        if ($settings->has('ticket', 'check-booking-time')) {
            $checkForBookingTime = $settings->get('ticket', 'check-booking-time')==1;
        }
        
        if ($origDateUnixTs<time() && $checkForBookingTime) {
            $success['success'] = false;
            $success['message'] = 'Booking Time Passed!';
            return $success;
        }
        
        //turn off multiple tickets if option is not defined
        if ($settings->has('ticket', 'max-tickets-gen-once')) {
            $count = $settings->get('ticket', 'max-tickets-gen-once');
        } else {
            $count = 1;
        }
        
        //get the requested route
        //first check if it has the start-end stops
        //if it does, great! Else check if it is return route
        //if none of the two matches, no such route here
        $customerRoute = Route::findOne($routeId);
        
        if (!$customerRoute->hasRoute($start, $end)) {
            //check if it is return reoute
            if ($customerRoute->returnR && $customerRoute->returnR->hasRoute($start, $end)) {
                //the stops suggests this is a return route not a go route
                $customerRoute = $customerRoute->returnR;
            } else {
                $success['success'] = false;
                $success['message'] = 'No Route/Stops for the Bus!';
                return $success;
            }
        }
        
        $currency = $card->currency;
        
        //find price
        $price = $card->price;
        
        if (empty($price) && intval($price)!=0) {
            $success['success'] = false;
            $success['message'] = 'No Price for  Route/Currency!';
            return $success;
        }

        //get planned routes for date and hour
        $bus = null;
        $busSeats = [];
        
        $message = "No Bus for " .Yii::$app->formatter->asDate($date, 'dd-M-yy')." ".$time ;
        
        $proute = null;
        
        $proute = null;
        
        $proutes = PlannedRoute::find()->where([
            'dept_date'=>$date,
            'dept_time'=>$time,
            'route'=>$customerRoute->id,
        ])->orderBy('priority ASC')->all();
        
        foreach ($proutes as $pr) {
            if ($this->isBusFull($pr->route, $pr->capacity, $pr->dept_date, $pr->dept_time, $start, $end, $pr->bus)) {
                $message = 'All buses are full for stops!';
                continue;
            } else {
                $proute = $pr;
                break;
            }
        }
         
         
        //no route found
        if (empty($proute)) {
            $success['success'] = false;
            $success['message'] = 'No Free Seats Bus for that Time!';
            return $success;
        } elseif ($proute->is_active==0) {
            $success['success'] = false;
            $success['message'] = 'Bus is Locked cannot sell';
            return $success;
        }

        $bus = Bus::findOne($proute->bus);
        
        if ($customerRoute) {
            //check if bus is not full
            if ($this->isBusFull($customerRoute->id, $proute->capacity, $date, $time, $start, $end, $bus->regno)) {
                //route have seats generate a ticket
                //$message = "Bus {$proute->bus} is full";
                $message = "Bus is full";
                $customerRoute = null; //just to make sure that it is null
            }
        } else {
            $message = 'Route does not Stops';
            $customerRoute = null; //just to make sure that it is null
        }
        
        //did we get any route?
        if (empty($customerRoute)) {
            $success['success'] = false;
            $success['message'] = $message;
        } else {
            //check seat if is free
            $busSeats = $this->getFreeSeats($customerRoute->id, $start, $end, $date, $time, $proute->capacity, $bus->regno);
            $seatCount = count($busSeats);
            if ($seatCount==0) {
                $success['success'] = false;
                //$success['message'] = "Bus {$proute->bus} is full";
                $success['message'] = "Bus is full";
                return $success;
            } elseif ($seatCount<$count) {
                $success['success'] = false;
                //$success['message'] = "{$seatCount} Seats for {$proute->bus} available";
                $success['message'] = "{$seatCount} Seats for the Bus available";
                return $success;
            }
            
            //no specific seat find any free
            $seat = $busSeats[0]; //first free seats

            //ticket
            //generate ticket
            $ticketModel = new Ticket;
            try {
                $ticketModel->ticket = 'NONE';
                $ticketModel->bus = $bus->regno;
                $ticketModel->seat = $seat;
                $ticketModel->route = $customerRoute->id;
                $ticketModel->start = $start;
                $ticketModel->end = $end;
                $ticketModel->dept_date = $date;
                $ticketModel->dept_time = $time;
                $ticketModel->customer = $customerModel->mobile;
                $ticketModel->is_staff = 0;
                $ticketModel->issued_on = time();
                $ticketModel->machine_serial = $data['pos'];
                $ticketModel->currency = $currency;

                $ticketModel->is_deleted = 0;
                
                $ticketModel->status = Ticket::STATUS_CARD_TICKET;
                $ticketModel->expired_in = 0;
                $ticketModel->discount = $disc;
                $ticketModel->price = $price;

                if ($ticketModel->save()) {
                    for ($i=0; $i<$count; $i++) {
                        $card->remaining_trips = $card->remaining_trips - 1;
			if($card->remaining_trips==0)
			{
				$card->is_sold=-1;
			}
                        if ($card->save(false)) {
                            //Log the card
                            $cl = new \app\models\CardLog;
                            $cl->attributes = [
                                'id'=>$ticketModel->id,
                                'card'=>$card->card,
                                'created_at'=>time(),
                                'created_by'=>Yii::$app->user->id,
                                'updated_at'=>time(),
                                'updated_by'=>Yii::$app->user->id,
                                'remained_trips'=>$card->remaining_trips
                            ];
                            $cl->save(false);
                        }
                        //increment points
                        $pstart = $ticketModel->start;
                        $pend = $ticketModel->end;
                            
                        if ($ticketModel->status == Ticket::STATUS_CARD_TICKET) {
                            if ($i>0) { //the first model, ignore if
                                $ticketModel->isNewRecord = true;
                                $ticketModel->id = null;
                                $ticketModel->ticket = null;
                                $ticketModel->seat = $busSeats[$i];
                            }
                            
                            $ticketModel->is_promo = 0;
                                
                            //generate the other remaining
                            if ($customerRoute->has_promotion==1) {
                                if ($customerRoute->is_return==1) {
                                    //to make life easier in handling points all return routes
                                    //are converted to going route equivalent and then inserted that way
                                    //GITEGA BUJA will be added as BUJA GITEGA
                                    $pstart = $ticketModel->end;
                                    $pend = $ticketModel->start;
                                }
                                
                                $pts = Point::findOne([
                                    'customer'=>$customerModel->mobile,
                                    'start'=>$pstart,
                                    'end'=>$pend
                                ]);
                                if (empty($pts)) {
                                    $pts = new Point;
                                    $pts->attributes = [
                                        'customer'=>$customerModel->mobile,
                                        'start'=>$pstart,
                                        'end'=>$pend,
                                        'points'=>1,
                                        'created_at'=>time(),
                                        'updated_at'=>time(),
                                    ];
                                    $pts->save();
                                } else {
                                    $pts->points = $pts->points+1;
                                    $pts->updated_at = time();
                                    $pts->save(false);
                                }
                                //check if it is promotion
                                $promo = $settings->get('ticket', 'promotion');
                                ;
                                if ($pts->points>$promo) {
                                    $ticketModel->is_promo = 1;
                                    $ticketModel->discount = $ticketModel->price;
                                    $pts->points = 0;
                                    $pts->save(false);
                                }
                            }
                        }

                        //update Ticket field and send results
                        $factory = new \RandomLib\Factory;
                        $generator = $factory->getGenerator(new \SecurityLib\Strength(\SecurityLib\Strength::MEDIUM));
                            
                        $ticketModel->ticket = $generator->generateString(12, 'ABCDEFGHIJKL09876MNOPQRSTUVWXYZ54321');
                        
                        //save changes
                        $ticketModel->save(false);

                        $success['success'] = true;
                                        //points
                        $points =  $this->db->createCommand('SELECT SUM(points) FROM Points WHERE customer=:customer AND start=:start AND end=:end')
                            ->bindValue(':customer', $ticketModel->customer)
                            ->bindValue(':start', $pstart)
                            ->bindValue(':end', $pend)
                            ->queryScalar();

                        if ($i==0) { //the first ticket, format the values
                            $success['message']['common'] = $this->formatTicket($ticketModel, $customerModel, true);
                        }
                        
                        $tinfo = [];
                        $tinfo['id'] = rtrim(chunk_split($ticketModel->ticket, 3, '-'), '-');
                        $tinfo['points'] = empty($points) ? '0' : $points;
                        $tinfo['is_promo'] = $ticketModel->is_promo;
                        $tinfo['seat'] = $ticketModel->seat;
                        
                        $success['message']['tickets'][] = $tinfo;
                         
                         //store the stuffs in the reserved table
                        $btnRoutes = $this->getPathRoutes($ticketModel->start, $ticketModel->end, $ticketModel->route);
                        //return ['r'=>$btnRoutes, 't'=>$ticketModel];
                        if (empty($btnRoutes)) {
                            //does not have babies so add it as a baby
                            $this->db->createCommand()->insert('ReservedSeats', [
                                'ticket' => $ticketModel->id,
                                'start' => $ticketModel->start,
                                'end' => $ticketModel->end,
                                'route' => $ticketModel->route,
                                'bus' => $ticketModel->bus,
                                'dept_date' => $ticketModel->dept_date,
                                'dept_time' => $ticketModel->dept_time,
                                'expires_in' => $ticketModel->expired_in,
                                'status' => $ticketModel->status,
                                'seat' => $ticketModel->seat,
                                'issued_on' => time(),
                            ])->execute();
                        } else {
                            foreach ($btnRoutes as $subroute) {
                                $this->db->createCommand()->insert('ReservedSeats', [
                                    'ticket' => $ticketModel->id,
                                    'start' => $subroute['start'],
                                    'end' => $subroute['end'],
                                    'route' => $ticketModel->route,
                                    'bus' => $ticketModel->bus,
                                    'dept_date' => $ticketModel->dept_date,
                                    'dept_time' => $ticketModel->dept_time,
                                    'expires_in' => $ticketModel->expired_in,
                                    'status' => $ticketModel->status,
                                    'seat' => $ticketModel->seat,
                                    'issued_on' => time(),
                                ])->execute();
                            }
                        }
                        //send sms?
                        if ($customerRoute->send_sms == 1) {
                            //1hr before boarding
                            //change hours to minutes
                            $timeExp = explode('H', $ticketModel->dept_time);
                            $totalMin = ($timeExp[0]*60)+$timeExp[1];
                            $totalMinDiff = $totalMin - 60;
                            //chenge back to 13H00 Format
                            $hr = intval($totalMinDiff/60);
                            $min = $totalMinDiff%60;
                            $time =sprintf("%02dH%02d", $hr, $min);
                            
                            //save SMS ready for sending
                            $this->db->createCommand()->insert('SMS', [
                                'ticket' => $ticketModel->id,
                                'customer' => $ticketModel->customer,
                                'route' => $ticketModel->route,
                                'dept_date' => $ticketModel->dept_date,
                                'dept_time' => $ticketModel->dept_time,
                                'message' =>"Hello! Ticket:{$ticketModel->ticket}, {$ticketModel->start}-{$ticketModel->end} Seat:{$ticketModel->seat}, {$ticketModel->dept_date},{$time}. Inquiries call {$customerRoute->customer_care}. Thanks for choosing VOLCANO EXPRESS",
                                'created_at'=>time(),
                                'updated_at'=>time(),
                                'created_by'=>Yii::$app->user->id,
                                'updated_by'=>Yii::$app->user->id,
                            ])->execute();
                        }
                    }
                    $success['message']['common']['remaining'] = $card->remaining_trips;
                } else {
                    $success['success'] = false;
                    $success['message'] = json_encode($ticketModel->errors); //'Could not generate ticket';
                }
            } catch (\yii\base\Exception $e) {
                $success['success'] = false;
                $success['message'] = 'System Error occured:'.$e->getMessage();
                $ticketModel->delete();
            }catch (\yii\db\IntegrityException $e) {
                $success['success'] = false;
                $success['message'] = 'Seat already sold to someone else. Try another one!';
                $ticketModel->delete();
            }
        }

        return $success;
    }
 protected function isBusFull($routeId, $busCapacity, $date, $time, $start, $end, $bus)
    {
        $subrouteFull = false;
        
        //get all routes between
        $btnRoutes = $this->getPathRoutes($start, $end, $routeId);
        foreach ($btnRoutes as $subroute) {
            //check if full on this route
            $occupied =  $this->db->createCommand('SELECT COUNT(*) FROM ReservedSeats 
                                WHERE start=:start AND end = :end AND route=:route AND bus=:bus AND dept_date = :date AND dept_time = :time
                                AND ((expires_in+issued_on)> :curr_time  OR (status = :statusConfirmed AND expires_in=0 ) OR (status = :statusCards AND expires_in=0 )) ')
                        ->bindValue(':start', $start)
                        ->bindValue(':end', $end)
                        ->bindValue(':route', $routeId)
                        ->bindValue(':bus', $bus)
                        ->bindValue(':date', $date)
                        ->bindValue(':time', $time)
                        ->bindValue(':curr_time', time())
                        ->bindValue(':statusConfirmed', Ticket::STATUS_CONFIRMED)
                        ->bindValue(':statusCards', Ticket::STATUS_CARD_TICKET)
                        ->queryScalar();
            if ($occupied>=$busCapacity) {
                $subrouteFull = true;
                break;
            }
        }
        //check each subroute if full then you cannot buy a seat at all
        //first check full route

        //$sold = Ticket::find()
            //->where(['route'=>$routeId, 'dept_date'=>$date, 'dept_time'=>$time])
            //->andWhere(['>', '`expired_in`+`created_at`', time()])
            //->andWhere(['is_deleted'=> '0'])
            //->orWhere(['expired_in'=> '0', 'status'=>Ticket::STATUS_CONFIRMED])
            //->count();
            
        return $subrouteFull;
    }


private function getPathRoutes($start, $end, $parent)
    {
        $sql = 'SELECT start, end FROM Routes  WHERE (parent = :parent AND  (idx BETWEEN (SELECT idx FROM Routes  WHERE parent = :parent AND start=:start ORDER BY idx ASC LIMIT 1) AND (SELECT idx FROM Routes  WHERE parent = :parent AND end=:end ORDER BY idx ASC LIMIT 1))) OR (parent=:parent AND idx BETWEEN (SELECT idx+1 FROM Routes  WHERE parent IS NULL AND id = :parent AND start=:start AND end=:end ORDER BY idx ASC LIMIT 1) AND (SELECT idx FROM Routes  WHERE parent = :parent ORDER BY idx DESC LIMIT 1))';
        $routes =  $this->db->createCommand($sql)
                        ->bindValue(':start', $start)
                        ->bindValue(':end', $end)
                        ->bindValue(':parent', $parent)
                        ->queryall();
        //add the parent route as the first
        //array_unshift($routes, ['start'=>$start, 'end'=>$end]);
        //var_dump($routes); die();
        return $routes;
    }

protected function getFreeSeats($routeId, $start, $end, $date, $time, $busCapacity, $busReg)
    {
        //clean expired booking for this bus. TDL if not performing, move it to cron job
        $seats = range(1, $busCapacity);
        //get all occupied seats from start to end
        $routes = $this->getPathRoutes($start, $end, $routeId);
        //add parent route as first subroute
        $mainRoute = $this->db->createCommand('SELECT start, end FROM Routes WHERE parent IS NULL AND id=:id')
            ->bindValue(':id', $routeId) 
            ->queryAll();
        
        $routes = array_unique(array_merge($routes, $mainRoute), SORT_REGULAR);
        //print_r($routes);
        //die();
        foreach($routes as $r)
        {
            $this->db->createCommand('UPDATE ReservedSeats SET status = "EX", seat = seat*-1 WHERE start=:start AND end=:end AND route=:route AND dept_date=:date AND dept_time = :time AND bus=:bus AND (status = "BO" OR status = "BT") AND  (UNIX_TIMESTAMP(CONCAT(dept_date, " ", REPLACE(dept_time, "H", ":"), ":00"))- expires_in)<=UNIX_TIMESTAMP(CONVERT_TZ(NOW(), "Africa/Kigali",  "'.Yii::$app->user->identity->timezone.'"))') //TDL When inserting BT ticket in mobile app make them x-minutes to expiry that is, set expires_in as 5min for example instead of 30min
             ->bindValue(':bus', $busReg)
             ->bindValue(':time', $time)
             ->bindValue(':date', $date)
             ->bindValue(':route', $routeId)
             ->bindValue(':start', $r['start'])
             ->bindValue(':end', $r['end'])
             ->execute();
        }
            
        $seatsQuery =  new Query;
        $seatsQuery->select('seat')
                   ->from('ReservedSeats')
                   ->where(['route'=>$routeId, 'dept_date'=>$date, 'dept_time'=>$time, 'bus'=>$busReg])
                   ->andWhere(['OR',
                        new \yii\db\Expression("(status='BO' OR status='BT') AND (UNIX_TIMESTAMP(CONCAT(dept_date, ' ', REPLACE(dept_time, 'H', ':'), ':00'))- expires_in)>UNIX_TIMESTAMP(CONVERT_TZ(NOW(), 'Africa/Kigali',  '".Yii::$app->user->identity->timezone."'))"), 
                        ['status'=>Ticket::STATUS_CONFIRMED],
                        ['status'=>Ticket::STATUS_CARD_TICKET]
                   ]); 
        //add reotes
        $where = [];
        $where[] = 'OR';
        $where[] = ['start'=>$start, 'end'=>$end];
        foreach ($routes as $route) {
            $where[] = $route;
        }
        $seatsQuery->andWhere(['or', $where])
                    ->groupBy('seat');
        $sold = $seatsQuery->createCommand($this->db)->queryColumn();
        //get diff btween seats total and
        $free = array_values(array_diff($seats, $sold));
        //print_r($sold);
        //die();
        return $free;
    }
    
    protected function getSeatDetails($proute, $start, $end)
    {
        $details = [];
        $details['total_seats'] = $proute->capacity;
        
        $seats = range(1, $proute->capacity);
        //get all occupied seats from start to end
        $routes = $this->getPathRoutes($start, $end, $proute->route);
        //add parent route as first subroute
        $mainRoute = $this->db->createCommand('SELECT start, end FROM Routes WHERE parent IS NULL AND id=:id')
            ->bindValue(':id', $proute->route) 
            ->queryAll();
        
        $routes = array_unique(array_merge($routes, $mainRoute), SORT_REGULAR);
        //print_r($routes);
        //die();
        foreach($routes as $r)
        {
            $this->db->createCommand('UPDATE ReservedSeats SET status = "EX", seat = seat*-1 WHERE start=:start AND end=:end AND route=:route AND dept_date=:date AND dept_time = :time AND bus=:bus AND (status = "BO" OR status = "BT") AND  (UNIX_TIMESTAMP(CONCAT(dept_date, " ", REPLACE(dept_time, "H", ":"), ":00"))- expires_in)<=UNIX_TIMESTAMP(CONVERT_TZ(NOW(), "Africa/Kigali",  "'.Yii::$app->user->identity->timezone.'"))') //TDL When inserting BT ticket in mobile app make them x-minutes to expiry that is, set expires_in as 5min for example instead of 30min
             ->bindValue(':bus', $proute->bus)
             ->bindValue(':date', $proute->dept_date)
             ->bindValue(':time', $proute->dept_time)
             ->bindValue(':route', $proute->route)
             ->bindValue(':start', $r['start'])
             ->bindValue(':end', $r['end'])
             ->execute();
        }
            
        $seatsQuery =  new Query;
        $seatsQuery->select('seat, status')
                   ->from('ReservedSeats')
                   ->where(['route'=>$proute->route, 'dept_date'=>$proute->dept_date, 'dept_time'=>$proute->dept_time, 'bus'=>$proute->bus])
                   ->andWhere(['OR',
                        new \yii\db\Expression("(status='BO' OR status='BT') AND (UNIX_TIMESTAMP(CONCAT(dept_date, ' ', REPLACE(dept_time, 'H', ':'), ':00'))- expires_in)>UNIX_TIMESTAMP(CONVERT_TZ(NOW(), 'Africa/Kigali',  '".Yii::$app->user->identity->timezone."'))"), 
                        ['status'=>Ticket::STATUS_CONFIRMED],
                        ['status'=>Ticket::STATUS_CARD_TICKET]
                   ]); 
        //add reotes
        $where = [];
        $where[] = 'OR';
        $where[] = ['start'=>$start, 'end'=>$end];
        foreach ($routes as $route) {
            $where[] = $route;
        }
        $seatsQuery->andWhere(['or', $where])
                    ->groupBy(['seat', 'status']);
                    
        $booked = $seatsQuery->createCommand($this->db)->queryAll();
        
        $free = [];
        $allSeats = [];

        $occupiedSeatNoStatus = [];
        
        foreach($booked as $bookedOne)
        {
            $allSeats[] = $bookedOne;
            $occupiedSeatNoStatus[] = $bookedOne['seat'];
        }
        //get free seats only
        $free = array_values(array_diff($seats, $occupiedSeatNoStatus));

        //add keys
        array_walk($free, function($value) use(&$allSeats){
            $allSeats[] = [
                'seat'=> $value,
                'status'=> Ticket::STATUS_FREE,
            ];
        });
        
        $details['seats'] = $allSeats;
        return $details;
    }

    protected function formatTicket($ticketModel, $customerModel=null, $isCommon=false)
    {
        if ($customerModel==null) {
            $customerModel = $ticketModel->customerR;
        }
        
        $route = $ticketModel->routeR;
        
        $ticket = [];
        $ticket['cc'] = $route->customer_care;
        if (!$isCommon) {
            $ticket['id'] = rtrim(chunk_split($ticketModel->ticket, 3, '-'), '-');
            $ticket['seat'] = $ticketModel->seat;
            $ticket['paid'] = $ticketModel->price - $ticketModel->discount;
        } else {
            $ticket['price'] = $ticketModel->price;
        } //since promo and staff ticket changes send just price and calc at POS level
        
        $boardingOffset =  $this->db->createCommand('SELECT offset FROM BoardingTimes WHERE route=:route AND start=:start AND end=:end')
                            ->bindValue(':route', $ticketModel->route)
                            ->bindValue(':start', $ticketModel->start)
                            ->bindValue(':end', $ticketModel->end)
                            ->queryScalar();
        if (empty($boardingOffset)) {
            $boardingOffset = 0;
        }
        
        //set time
        $time = $ticketModel->dept_time;
        if ($boardingOffset>0) {
            //change hours to minutes
            $timeExp = explode('H', $time);
            $totalMin = ($timeExp[0]*60)+$timeExp[1];
            $totalMinDiff = $totalMin + $boardingOffset;
            //chenge back to 13H00 Format
            $hr = intval($totalMinDiff/60);
            $min = $totalMinDiff%60;
            $time =sprintf("%02dH%02d", $hr, $min);
        }
        
        $ticket['route'] = empty($route->parentR) ? $route->name :$route->parentR->name;//use parent route only
        $ticket['journey'] = "{$ticketModel->start} - {$ticketModel->end} ";
        $ticket['date'] = $ticketModel->dept_date;
        $ticket['time'] = $time;
        $ticket['pos'] = $ticketModel->machine_serial;
        $ticket['name'] = strlen($customerModel->mobile)>12 ? $customerModel->name : $customerModel->name.' - '.$ticketModel->customer; //$customerModel->name.' - ***'.(substr($ticketModel->customer, 7));
        
        $setting = Yii::$app->settings;
        if($setting->has('ticket', 'print-bus-plate') && $setting->get('ticket', 'print-bus-plate')==1) 
            $ticket['route'] = "{$ticket['route']}\nBus: {$ticketModel->bus}";
        
        if($setting->has('ticket', 'print-cashier-name') && $setting->get('ticket', 'print-cashier-name')==1) 
        {
            $time = Yii::$app->formatter->asDatetime(time(), 'short');
            $user = Yii::$app->user->identity;
            $ticket['generated'] = "{$time}\nCashier: {$user->name}";
        }
        else
            $ticket['generated'] = Yii::$app->formatter->asDatetime(time(), 'short');
        
        $ticket['discount'] = $ticketModel->discount;

        if ($ticketModel->status == Ticket::STATUS_CONFIRMED||$ticketModel->status == Ticket::STATUS_CARD_TICKET) {
            if (!$isCommon) {
                $ticket['is_promo'] = is_null($ticketModel->is_promo) ? 0 : $ticketModel->is_promo;
                //points
                $points =  $this->db->createCommand('SELECT SUM(points) FROM Points WHERE customer=:customer AND start=:start AND end=:end')
                            ->bindValue(':customer', $ticketModel->customer)
                            ->bindValue(':start', $ticketModel->start)
                            ->bindValue(':end', $ticketModel->end)
                            ->queryScalar();
                if (empty($points)) {
                    $points = "0";
                }

                $ticket['points'] = $points;
            }
            $ticket['is_staff'] = is_null($ticketModel->is_staff) ? 0 :$ticketModel->is_staff;
            $ticket['is_intl'] = $route->is_intl;
            //customer details for intl route
            if ($route->is_intl) {
                $ticket['passport'] = $customerModel->passport;
                $ticket['nationality'] = $customerModel->nationality;
                $ticket['from_nation'] = $customerModel->from_nation;
                $ticket['to_nation'] = $customerModel->to_nation;
                $ticket['gender'] = $customerModel->gender == 1 ? 'Male' : 'Female';
            }
        } elseif ($ticketModel->status == Ticket::STATUS_BOOKED) {
            $deptTimeUnix = strtotime($ticketModel->dept_date.' '.str_replace('H', ':', $ticketModel->dept_time));
            $expiryTime = $deptTimeUnix-$ticketModel->expired_in;

            $ticket['expires'] = Yii::$app->formatter->asDatetime($expiryTime, 'short');
            ;
        }
        //all need currency
        $ticket['currency'] = $ticketModel->currency;
        $ticket['bus'] = $ticketModel->bus;

        return $ticket;
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
                $success['message'] = "Sell Card No:{$cards->card} for {\n\n\n";
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