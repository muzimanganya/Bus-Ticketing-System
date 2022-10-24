<?php

namespace app\controllers;

use Yii;
use app\models\SysLog;
use app\models\CapacityChangeForm;
use yii\web\Response;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\db\Expression;
use app\models\PlannedRoute;
use app\models\Ticket;
use app\models\TicketSearch;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use yii\data\SqlDataProvider;
use yii\data\ActiveDataProvider;


use yii\db\Query;

class SiteController extends TenantController
{

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }
 
    public function actionIndex($date = null)
    {
        if (is_null($date)) {
            $date = date('Y-m-d');
        }
        $sql = 'SELECT rname AS name, route_id AS route, IFNULL(tickets, 0) AS tickets, IFNULL(RWF, 0) AS RWF, IFNULL(FIB, 0) AS FIB, IFNULL(UGS, 0) AS UGS, IFNULL(USD, 0) AS USD FROM (SELECT CONCAT(start,"-", end) AS rname, id AS route_id FROM Routes WHERE parent IS NULL) r LEFT JOIN (SELECT route, COUNT(ticket) AS tickets, SUM(CASE WHEN currency= "RWF" THEN price ELSE 0 END) AS RWF, SUM(CASE WHEN currency= "FIB" THEN price-discount ELSE 0 END) AS FIB, SUM(CASE WHEN currency= "UGS" THEN price-discount ELSE 0 END) AS UGS, SUM(CASE WHEN currency= "USD" THEN price-discount ELSE 0 END) AS USD FROM Tickets WHERE dept_date =  :date AND (status=\'CO\' OR status=\'CT\') GROUP BY route) t ON  t.route = r.route_id ORDER BY route ASC ';
            
        $count = $this->db->createCommand('SELECT COUNT(*) FROM Routes WHERE parent IS NULL')->queryScalar();

        $dataProvider = new SqlDataProvider([
            'sql' =>$sql,
            'params' => [':date' => $date],
            'totalCount' => $count,
            'db'=>$this->db,
            'sort' => [
                'attributes' => [
                    'route',
                    'name',
                    'tickets',
                    'RWF',
                    'FIB',
                    'UGS',
                    'USD',
                ],
            ],
            'pagination' => [
                'pageSize' => 25,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'date'=>$date,
        ]);
    }


    public function actionRouteDetails($id, $date=null)
    {
        if (is_null($date)) {
            $date = date('Y-m-d');
        }
             
        $sql = 'SELECT pr.bus, pr.capacity, pr.route, r.start, r.end, pr.dept_date, pr.dept_time, IFNULL(t.tickets, 0) AS tickets, IFNULL(RWF, 0) AS RWF, IFNULL(FIB, 0) AS FIB, IFNULL(UGS, 0) AS UGS, IFNULL(USD, 0) AS USD FROM 	(SELECT route, dept_date, dept_time, bus, capacity FROM PlannedRoutes p WHERE route =  :route AND dept_date =   :date) pr LEFT JOIN 	 	(SELECT  route, dept_date, dept_time, COUNT(ticket) AS tickets, SUM(CASE WHEN currency= "RWF" THEN price-discount ELSE 0 END) AS RWF, SUM(CASE WHEN currency= "FIB" THEN price-discount ELSE 0 END) AS FIB, SUM(CASE WHEN currency= "UGS" THEN price-discount ELSE 0 END) AS UGS, SUM(CASE WHEN currency= "USD" THEN price-discount ELSE 0 END) AS USD  FROM Tickets t WHERE route =  :route AND dept_date =  :date AND (status=\'CO\' OR status=\'CT\')  GROUP BY dept_date, dept_time) t ON t.route = pr.route AND t.dept_date = pr.dept_date AND t.dept_time = pr.dept_time INNER JOIN Routes r ON pr.route = r.id';
            
        $count = $this->db->createCommand('SELECT COUNT(*) FROM PlannedRoutes WHERE route = :route AND dept_date = :date', [
            ':date'=>$date, ':route'=>$id
        ])->queryScalar();

        $dataProvider = new SqlDataProvider([
            'sql' =>$sql,
            'params' => [':date' => $date, ':route'=>$id],
            'totalCount' => $count,
            'db'=>$this->db,
            'sort' => [
                'attributes' => [
                    'route',
                    'start',
                    'end',
                    'dept_date',
                    'dept_time',
                    'tickets',
                    'RWF',
                    'FIB',
                    'UGS',
                    'USD',
                ],
            ],
            'pagination' => [
                'pageSize' => 25,
            ],
        ]);

        return $this->render('route-details-new', [
            'dataProvider' => $dataProvider,
            'date' => $date,
        ]);
    }
    
    public function actionGetSoldSeats($route, $start, $end, $date, $time, $capacity)
    {
        $seats = range(1, $capacity);
        //get all occupied seats from start to end
        $seatsQuery =  new Query;
        $seatsQuery->select(['seat', 'ticket', 'status', 'rname'=>'CONCAT(`start`, " - ", `end`)', 'owner'=>'CONCAT(name, " - ", mobile)'])
                   ->from('Tickets')
                   ->innerJoin('Customers', 'Tickets.customer = Customers.mobile')
                   ->where(['route'=>$route, 'dept_date'=>$date, 'dept_time'=>$time])
                   ->andWhere(['OR',
                        ['>',new \yii\db\Expression('`expired_in`+`issued_on`'), time()],
                        ['status'=>Ticket::STATUS_CONFIRMED]
                   ]);
        //add reotes
        //$routes = $this->getPathRoutes($start, $end, $route);
        $where = [];
        $where[] = 'OR';
        $where[] = ['start'=>$start, 'end'=>$end];
        //foreach($routes as $route)
        //{
            //$where[] = $route;
        //}
        $seatsQuery->andWhere(['or', $where]);
                    
        $seatsQuery->groupBy('seat, ticket, customer, status')
                    ->addGroupBy(new Expression('CONCAT(start, " - ", end)'))
                    ->orderBy('seat');
        $sold = $seatsQuery->createCommand($this->db)->queryAll();
        $return = [];
        foreach ($sold as $ticket) {
            $return[] = array_merge($ticket, [
                'cancel'=>Url::to(['cancel-ticket', 'id'=>$ticket['ticket']]),
                'change_seat'=>Url::to(['change-seat', 'id'=>$ticket['ticket']]),
            ]);
        }
        return json_encode($return);
    }
    
    public function actionCancelTicket($id)
    {
        $success = ['success'=>false, 'message'=>'Failed to cancel the ticket'];
        Yii::$app->response->format = Response::FORMAT_JSON;
        $ticket = Ticket::find()->where(['ticket'=>$id])->one();
        $ticket->status = Ticket::STATUS_CANCELLED;
        if ($ticket->save(false)) {
            $success['success'] = true;
            $success['message'] = "Ticket $id was cancelled!";
            $this->db->createCommand()->update('ReservedSeats', ['status' =>Ticket::STATUS_CANCELLED], ['ticket'=>$ticket->id])->execute();
        }
        return $success;
    }
    
    public function actionChangeSeat($id, $seat)
    {
        $success = ['success'=>false, 'message'=>'Failed to change seat'];
        Yii::$app->response->format = Response::FORMAT_JSON;
        $ticket = Ticket::find()->where(['ticket'=>$id])->one();
        
        if ($ticket->isSeatOccupied($seat)) {
            $success['success'] = false;
            $success['message'] = "Ticket $id is accupied!";
            return $success;
        }
        
        $ticket->seat = $seat;
        if ($ticket->save(false)) {
            $success['success'] = true;
            $success['message'] = "Ticket $id seat changed to $seat";
            
            $this->db->createCommand()->update('ReservedSeats', ['seat' =>$seat], ['ticket'=>$ticket->id])->execute();
        }
        return $success;
    }
    
    public function actionMoveCustomer()
    {
        $model = new \app\models\ChangeCustomerBus();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
            
                $ticket = Ticket::find()->where(['ticket'=>$model->ticket])->one();
                //do not update times and editor as they will own the ticket
                $ticket->detachBehavior('blame');
                $ticket->detachBehavior('time');
                
                $beforeChange = "{$ticket->dept_time}";
                
                $plannedRoute = PlannedRoute::find()->where([
                    'route'=>$ticket->route,
                    'dept_date'=>$model->to_date,
                    'dept_time'=>$model->to_time ,
                    'is_active'=>1
                ])->one();
                
                if ($plannedRoute->dept_date == $ticket->dept_date && $plannedRoute->dept_time == $ticket->dept_time) {
                    $model->addError('to_time', 'Customer is already in this bus');
                } else {
                    $ticket->dept_date = $plannedRoute->dept_date;
                    $ticket->dept_time = $plannedRoute->dept_time;
                    $ticket->bus = $plannedRoute->bus;
                    $ticket->seat = $model->seat;
                    if ($ticket->save(false)) {
                        $this->db->createCommand()->update('ReservedSeats', [
                            'bus' => $plannedRoute->bus,
                            'seat'=>$ticket->seat,
                            'route'=>$ticket->route,
                            'dept_date'=>$ticket->dept_date,
                            'dept_time'=>$ticket->dept_time
                            ],
                            ['ticket'=>$ticket->id] //where
                        )->execute();
                        
                        $user = Yii::$app->user->identity;
                        $comment = "{$user->name} - {$user->mobile} Changed customer from {$beforeChange} to {$plannedRoute->dept_time}";
                        if (!empty($comment)) {
                            $comment =  $comment.' ('.$model->comment.')';
                        }
                            
                        $log = new SysLog;
                        $log->attributes = [
                            'category'=>SysLog::LOG_CAT_BUS_CHANGE,
                            'reference'=>$ticket->id.'',
                            'comment'=>$comment,
                            'created_at'=>time(),
                            'updated_at'=>time(),
                            'created_by'=>Yii::$app->user->id, 
                            'updated_by'=>Yii::$app->user->id,
                        ];
                        if ($log->save()) {
                            return $this->redirect(['reports/bus-details', 'id'=>base64_encode($plannedRoute->dept_date.';'.$plannedRoute->route.';'.$plannedRoute->dept_date.';'.$plannedRoute->dept_time.';'.$plannedRoute->bus)]);
                        }
                    }
                }
            }
        }

        return $this->render('moveCustomer', [
            'model' => $model,
        ]);
    }
    
    public function actionTicketDetails($ticket)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $success = [];
        $model = Ticket::find()->where(['ticket'=>$ticket])->one();
        if (empty($model)) {
            $success['message'] = "Ticket $ticket Not Found";
            return $success;
        }
        $customer = $model->customer.' - '.$model->customerR->name;
        $journey = "{$model->start} - {$model->end} | {$model->routeR->name} | {$model->dept_date} {$model->dept_time}";
        $msg = "<h3>$customer&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$journey</h3>";
        $success['message'] = $msg;
        
        return $success;
    }


    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        $this->layout = 'login';
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    
    public function actionShowRouteLogs()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $data = Yii::$app->request->post('data');

        $dataProvider = new ActiveDataProvider([
            'query' => SysLog::find()->where(['reference'=>json_encode($data), 'category'=>'CC'])->orderBy('created_at DESC'),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return ['success'=>true, 'message'=>$this->renderAjax('_showRouteLogs', ['dataProvider'=>$dataProvider])];
    }
    
    
    public function actionChangeRouteCapacity()
    {
        $success = ['success'=>false]; 

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $model = new CapacityChangeForm();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) 
            {
                $pk = json_decode($model->pk, true);
                //remove capacity is not part of PK
                unset($pk['capacity']);
                $pmodel = PlannedRoute::findOne($pk);
                if($pmodel == null)
                {
                    $success['message'] = null;
                    return $success;
                }
                else if($pmodel->capacity == $model->capacity)
                {
                    $model->addError('capacity', 'Capacity have not changed!');
                    $success['message'] = $this->renderAjax('_changeRouteCapacity', [
                     'model' => $model,
                    ]);
                    return $success;
                }

                $oldCapacity = $pmodel->capacity;
                $pmodel->capacity = $model->capacity;
                if($pmodel->save())
                {
                     $user = Yii::$app->user->identity;

                    $comment = "{$user->name}-{$user->mobile} Updated Bus {$pmodel->dept_date} {$pmodel->dept_time} of {$pmodel->routeR->start}-{$pmodel->routeR->end}  with Capacity $oldCapacity TO Capacity {$pmodel->capacity} and comment:{$model->comment}";
                    //save Log
                    $log = new SysLog;
                    $log->attributes = [
                        'category'=>SysLog::LOG_CAT_CAPACITY_CHANGE,
                        'reference'=>json_encode($pk),
                        'comment'=>$comment,
                        'created_at'=>time(),
                        'updated_at'=>time(),
                        'created_by'=>Yii::$app->user->id,
                        'updated_by'=>Yii::$app->user->id,
                    ];
                    $log->save();

                    $success['success'] = true; 
                }
            }
            else 
                $success['message'] = $this->renderAjax('_changeRouteCapacity', [
                 'model' => $model,
                ]);
        }
        else 
            $success['message'] = $this->renderAjax('_changeRouteCapacity', [
             'model' => $model,
            ]);

        return $success;
    }
    
    private function getPathRoutes($start, $end, $parent)
    {
        $sql = 'SELECT start, end FROM Routes  WHERE parent = :parent AND  idx BETWEEN (SELECT idx FROM Routes  WHERE parent = :parent AND start=:start ORDER BY idx ASC LIMIT 1) AND (SELECT idx FROM Routes  WHERE parent = :parent AND end=:end ORDER BY idx ASC LIMIT 1)';
        $routes =  $this->db->createCommand($sql)
                        ->bindValue(':start', $start)
                        ->bindValue(':end', $end)
                        ->bindValue(':parent', $parent)
                        ->queryall();
        //add the parent route as the first
        //array_unshift($routes, ['start'=>$start, 'end'=>$end]);
        
        return $routes;
    }
}
