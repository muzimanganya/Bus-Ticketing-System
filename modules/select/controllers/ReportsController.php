<?php

namespace app\controllers;

use app\models\TicketSearch;
use app\models\Calendar;
use app\models\RouteCardSearch;
use app\models\TenantModel;
use app\models\Customer;
use app\models\Staff;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use app\models\SysLogSearch;
use app\models\PlannedRouteSearch;
use yii\data\SqlDataProvider;
use app\models\Ticket;
use app\models\PlannedRoute;
use Yii;

class ReportsController extends TenantController
{
    
    public function actionBooking($route=null, $date=null, $time=null)
    {
        Yii::$app->formatter->defaultTimeZone = Yii::$app->user->identity->timezone;

        if (is_null($date)) {
            $date = date('Y-m-d');
        }
                        
        $searchModel = new TicketSearch();
        $dataProvider = $searchModel->searchBooking($route, $date, $time);

        return $this->render('booking', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'date' => $date,
            'time' => $time,
            'route' => $route,
        ]);
    }

    public function actionCustomer()
    {
        $model = new PlannedRoute();

        if ($model->load(Yii::$app->request->post())) {
            return $this->render('customer-manifesto', [
                'dataProvider'=>$model->getTravellers(),
                'model'=>$model,
            ]);
        } else {
            return $this->render('customer', [
                'model'=>$model
            ]);
        }
    }

    public function actionIndex()
    {
        $model = new \app\models\FindReport();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                //redirect to report with date appended
                return $this->redirect([$model->report, 'date'=>$model->date, 'reference'=>$model->reference]);
            }
        }

        return $this->render('index', [
            'model' => $model,
        ]);
    }

    public function actionPromotion($route=null, $date=null)
    {
        if (is_null($date)) {
            $date = date('Y-m-d');
        }
            
        $searchModel = new TicketSearch();
        $dataProvider = $searchModel->searchPromo($route, $date);

        return $this->render('promotion', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'date' => $date,
            'route' => $route,
        ]);
    }

    public function actionMonthlySales()
    {
        $calendar = new Calendar();

        if (!$calendar->load(Yii::$app->request->post())) {
            $calendar->month = date('m');
            $calendar->year = date('Y');
        }
            
        $count = $this->db->createCommand('SELECT COUNT(*) FROM (SELECT route  FROM Tickets t WHERE MONTH(FROM_UNIXTIME(t.created_at)) = :month AND YEAR(FROM_UNIXTIME(t.created_at)) = :year GROUP BY t.route) tt', [':month' => $calendar->month,':year'=>$calendar->year])->queryScalar();

        $sql ='SELECT route, CONCAT(r.start, "-", r.end) AS stops, SUM(CASE WHEN is_promo=0 THEN 1 ELSE 0 end) AS tickets, SUM(CASE WHEN is_promo=1 THEN 1 ELSE 0 end) AS promo, COUNT(DISTINCT machine_serial) AS pos, SUM(CASE WHEN currency="RWF" AND is_promo=0 THEN  price ELSE 0 END) AS RWF, SUM(CASE WHEN currency="FIB" AND is_promo=0 THEN  price ELSE 0 END) AS FIB, SUM(CASE WHEN currency="UGS" AND is_promo=0 THEN  price ELSE 0 END) AS UGS, SUM(CASE WHEN currency="USD" AND is_promo=0 THEN  price ELSE 0 END) AS USD FROM Tickets t INNER JOIN Routes r ON r.id = t.route WHERE t.status="CO" AND MONTH(FROM_UNIXTIME(t.created_at)) = :month AND YEAR(FROM_UNIXTIME(t.created_at)) = :year GROUP BY t.route';
        
        $dataProvider = new SqlDataProvider([
            'sql' => $sql,
            'db'=>$this->db,
            'params' => [':month' => $calendar->month,':year'=>$calendar->year],
            'totalCount' => $count,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        
        return $this->render('monthly-sales', [
            'calendar'=>$calendar, 
            'dataProvider'=>$dataProvider
        ]);
    }
    
    public function actionMonthlyRoute($route, $month = null, $year = null)
    {
        $calendar = new Calendar();

        if (!$calendar->load(Yii::$app->request->post())) {
            if(is_null($month))
                $calendar->month = date('m');
            else
                $calendar->month = $month;
            if(is_null($year))    
                $calendar->year = date('Y');
            else
                $calendar->year = $year;
        }
            
        $routeTitle = $this->db->createCommand('SELECT name  FROM Routes WHERE id = :route', [':route'=>$route])->queryScalar();
        $count = $this->db->createCommand('SELECT COUNT(*) FROM (SELECT CONCAT(start, "-", end)  FROM Tickets t WHERE MONTH(FROM_UNIXTIME(t.created_at)) = :month AND YEAR(FROM_UNIXTIME(t.created_at)) = :year AND t.route = :route GROUP BY CONCAT(start, "-", end)) tt', [':month' => $calendar->month,':year'=>$calendar->year, ':route'=>$route])->queryScalar();
        

        $sql ='SELECT route, CONCAT(t.start, "-", t.end) AS stops, SUM(CASE WHEN is_promo=0 THEN 1 ELSE 0 end) AS tickets, SUM(CASE WHEN is_promo=1 THEN 1 ELSE 0 end) AS promo, COUNT(DISTINCT machine_serial) AS pos, SUM(CASE WHEN currency="RWF" AND is_promo=0 THEN  price ELSE 0 END) AS RWF, SUM(CASE WHEN currency="FIB" AND is_promo=0 THEN  price ELSE 0 END) AS FIB, SUM(CASE WHEN currency="UGS" AND is_promo=0 THEN  price ELSE 0 END) AS UGS, SUM(CASE WHEN currency="USD" AND is_promo=0 THEN  price ELSE 0 END) AS USD FROM Tickets t INNER JOIN Routes r ON r.id = t.route WHERE MONTH(FROM_UNIXTIME(t.created_at)) = :month AND YEAR(FROM_UNIXTIME(t.created_at)) = :year AND route = :route GROUP BY CONCAT(start, "-", end) ORDER BY route';
        
        $dataProvider = new SqlDataProvider([
            'sql' => $sql,
            'db'=>$this->db,
            'params' => [':month' => $calendar->month,':year'=>$calendar->year, ':route'=>$route],
            'totalCount' => $count,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        
        return $this->render('monthly-sales-route', [
            'calendar'=>$calendar, 
            'route'=>$routeTitle, 
            'dataProvider'=>$dataProvider
        ]);
    }
    
    public function actionSales($route=null, $date=null)
    {
        if (is_null($date)) {
            $date = date('Y-m-d');
        }
            
        if (is_null($route)) {
            //$route = $this->db->createCommand('SELECT route FROM Tickets WHERE DATE(FROM_UNIXTIME(created_at)) = :date LIMIT 1')
            $route = $this->db->createCommand('SELECT route FROM Tickets WHERE dept_date = :date LIMIT 1')
                ->bindValue(':date', $date)
                ->queryScalar();
        }
        
        $searchModel = new TicketSearch();
        $searchModel->load(Yii::$app->request->queryParams);
        $searchModel->route = $route;
        
        $dataProvider = $searchModel->searchRoutes($date);

        return $this->render('sales', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'date'=>$date,
            'route'=>$route,
        ]);
    }
    
    public function actionBusDetails($id, $startEnd=null)
    {
        $details = explode(';', base64_decode($id));
        $date = $details[0];
        $route = $details[1];
        $dept_date = $details[2];
        $dept_time = $details[3];
        
        $start = null;
        $end = null;
        
        if (empty($startEnd)) {
            $ticket = Ticket::find()->where([
                'route'=>$route,
                'dept_date'=>$dept_date,
                'dept_time'=>$dept_time
            ])->one();
            if (!empty($ticket)) {
                $startEnd = "{$ticket->start}-{$ticket->end}";
                $start = $ticket->start;
                $end = $ticket->start;
            }
        }
        
        if (!empty($startEnd)) {
            $expStartEnd = explode('-', $startEnd);
            $start = $expStartEnd[0];
            $end = $expStartEnd[1];
        }
        
        $searchModel = new TicketSearch();
        
        $searchModel->attributes = [
            'route'=>$route,
            'dept_date'=>$dept_date,
            'dept_time'=>$dept_time,
            'start'=>$start,
            'end'=>$end,
        ];
        $dataProvider = $searchModel->searchBusDetails();
        
        //do show tickets for 3 days ago as today might be missing
        $dept_date = date_create($dept_date);
        date_sub($dept_date, date_interval_create_from_date_string("1 days"));

        return $this->render('bus-details', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'date' => $date,
            'dept_time' =>$dept_time,
            'dept_date' =>date_format($dept_date, "Y-m-d"),
            'route' => $route,
            'startEnd' => $startEnd,
        ]);
    }

    public function actionTrends()
    {
        $searchModel = new TicketSearch();
        
        $weeklySales = $searchModel->searchWeeklySales();
        $bestSellers = $searchModel->searchBestSellers();
        $bestRoutes = $searchModel->searchBestRoutes();
        
        return $this->render('trends', [
            'weekly'=>$weeklySales,
            'bestSellers'=>$bestSellers,
            'bestRoutes'=>$bestRoutes,
        ]);
    }
    
    public function actionUserSales($date=null)
    {
        $date = is_null($date)? date('Y-m-d') : $date;
    
        $searchModel = new TicketSearch();
        $dataProvider = $searchModel->searchUserSales($date);
        
        return $this->render('user-report', [
            'dataProvider'=>$dataProvider,
            'date' => $date,
        ]);
    }
    
    public function actionUserSalesDetails($date, $user)
    {
        $count = $this->db->createCommand('SELECT COUNT(ticket) FROM Tickets WHERE created_by=:author AND DATE(FROM_UNIXTIME(updated_at))=:date AND status<>"BO" GROUP BY machine_serial')
            ->bindValue(':author', $user)
            ->bindValue(':date', $date)
            ->queryScalar();

        $dataProvider = new SqlDataProvider([
            'sql' => 'SELECT machine_serial AS pos, SUM(CASE WHEN t.currency= "RWF" THEN price-discount ELSE 0 END) AS RWF , SUM(CASE WHEN t.currency= "UGS" THEN price-discount ELSE 0 END) AS UGS, SUM(CASE WHEN t.currency= "FIB" THEN price-discount ELSE 0 END) AS FIB, SUM(CASE WHEN t.currency= "USD" THEN price-discount ELSE 0 END) AS USD FROM Tickets t WHERE created_by=:author AND DATE(FROM_UNIXTIME(updated_at)) = :date  AND status<>"BO" GROUP BY machine_serial',
            'params' => [':author' => $user, ':date'=>$date],
            'totalCount' => $count,
            'db' => Ticket::getDb(),
            'pagination' =>false,
            'sort' => [
                'attributes' => [
                    'pos',
                ],
            ],
        ]);
        return $this->render('user-report-details', [
            'dataProvider'=>$dataProvider,
            'date' => $date,
            'name' => $user,
        ]);
    }
    
    
    public function actionPosSalesDetails($date, $pos)
    {
        $count = $this->db->createCommand('SELECT COUNT(ticket) FROM Tickets WHERE machine_serial=:pos AND DATE(FROM_UNIXTIME(updated_at))=:date GROUP BY updated_by')
            ->bindValue(':pos', $pos)
            ->bindValue(':date', $date)
            ->queryScalar();

        $dataProvider = new SqlDataProvider([
            'sql' => 'SELECT s.name AS author, COUNT(t.ticket) AS tickets, SUM(CASE WHEN t.currency= "RWF" THEN price-discount ELSE 0 END) AS RWF , SUM(CASE WHEN t.currency= "UGS" THEN price-discount ELSE 0 END) AS UGS, SUM(CASE WHEN t.currency= "FIB" THEN price-discount ELSE 0 END) AS FIB, SUM(CASE WHEN t.currency= "USD" THEN price-discount ELSE 0 END) AS USD FROM Tickets t  INNER JOIN volcano_shared.Staffs s ON s.mobile = t.updated_by WHERE machine_serial=:pos AND DATE(FROM_UNIXTIME(t.updated_at)) = :date AND status=\'CO\' GROUP BY t.updated_by, s.name',
            'params' => [':pos' => $pos, ':date'=>$date],
            'totalCount' => $count,
            'db' => Ticket::getDb(),
            'pagination' =>false,
            'sort' => [
                'attributes' => [
                    'pos',
                ],
            ],
        ]);
        return $this->render('pos-report-details', [
            'dataProvider'=>$dataProvider,
            'date' => $date,
            'pos' => $pos,
        ]);
    }
    
    public function actionPosSales($date=null)
    {
        $date = is_null($date)? date('Y-m-d') : $date;
    
        $searchModel = new TicketSearch();
        $dataProvider = $searchModel->searchPosSales($date);
        
        return $this->render('pos-report', [
            'dataProvider'=>$dataProvider,
            'date' => $date,
        ]);
    }
    
    public function actionCardSales($date=null, $reference=null)
    {
        $searchModel = new RouteCardSearch();
        $dataProvider = $searchModel->searchDailySales($date, $reference);
        $total = 0;
        foreach ($dataProvider->getModels() as $model) {
            $total = $total+($model->price*$model->total_trips);
        }

        return $this->render('card-sales', [
            'dataProvider' => $dataProvider,
            'total' => $total,
            'date' => $date,
            'mobile' => $reference,
        ]);
    }
    
    public function actionPlanned($date=null)
    {
        $date = is_null($date)? date('Y-m-d') : $date;
        
        $searchModel = new PlannedRouteSearch();
        $searchModel->dept_date = $date;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('planned-routes', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    
    public function actionLogs()
    {
        $searchModel = new SysLogSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('logs', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionFindLogTicket()
    {
        $ticket = Yii::$app->request->post('ticket');
        $model = Ticket::find()->where(['ticket'=>$ticket])->one();

        $searchModel = new SysLogSearch();
        if($model)
        {
            $model->reference = $model->id;
        }

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('logs', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    
    public function actionCustomerTickets($date, $reference)
    {
        $customer = Customer::findOne($reference);
        if (empty($customer)) {
            throw new NotFoundHttpException("Customer Not Found. Please Check if Reference $reference is Correct");
        }
        
        $dataProvider = null;
        
        if (empty($date)) {
            $count = $this->db->createCommand('SELECT COUNT(ticket) FROM Tickets WHERE customer=:customer  AND (status="CO" OR status="CT")')
                ->bindValue(':customer', $reference)
                ->queryScalar();

            $dataProvider = new SqlDataProvider([
                'sql' => 'SELECT t.*, s.name, s.mobile FROM Tickets t INNER JOIN volcano_shared.Staffs s ON s.mobile = t.created_by WHERE customer=:customer  AND (status="CO" OR status="CT")',
                'params' => [':customer' => $reference],
                'totalCount' => $count,
                'db' => Ticket::getDb(),
                'pagination' =>false,
                'sort' => [
                    'attributes' => [
                        'ticket',
                        'start',
                        'end',
                        'price',
                    ],
                ],
            ]);
        } else {
            $count = $this->db->createCommand('SELECT COUNT(ticket) FROM Tickets WHERE customer=:customer AND DATE(FROM_UNIXTIME(created_at))=:date AND (status="CO" OR status="CT")')
                ->bindValue(':customer', $reference)
                ->bindValue(':date', $date)
                ->queryScalar();

            $dataProvider = new SqlDataProvider([
                'sql' => 'SELECT t.*, s.name, s.mobile FROM Tickets t INNER JOIN volcano_shared.Staffs s ON s.mobile = t.created_by WHERE customer=:customer AND DATE(FROM_UNIXTIME(t.created_at))=:date AND (status="CO" OR status="CT") ',
                'params' => [':customer' => $reference, ':date'=>$date],
                'totalCount' => $count,
                'db' => Ticket::getDb(),
                'pagination' =>false,
                'sort' => [
                    'attributes' => [
                        'ticket',
                        'start',
                        'end',
                        'price',
                    ],
                ],
            ]);
        }
        return $this->render('customer-tickets', [
            'dataProvider'=>$dataProvider,
            'date' => $date,
            'customer' =>$customer ,
        ]);
    }
    
    
    public function actionCashierTickets($date, $reference)
    {
        $staff = Staff::findOne($reference);
        if (empty($staff)) {
            throw new NotFoundHttpException("Cashier Not Found. Please Check if Reference $reference is Correct");
        }
    
        $count = $this->db->createCommand('SELECT COUNT(ticket) FROM Tickets WHERE updated_by=:staff AND DATE(FROM_UNIXTIME(updated_at))=:date')
            ->bindValue(':staff', $reference)
            ->bindValue(':date', $date)
            ->queryScalar();

        $dataProvider = new SqlDataProvider([
            'sql' => 'SELECT t.*, s.name, s.mobile FROM Tickets t INNER JOIN volcano_shared.Staffs s ON s.mobile = t.updated_by WHERE t.updated_by=:staff AND DATE(FROM_UNIXTIME(t.updated_at))=:date',
            'params' => [':staff' => $reference, ':date'=>$date],
            'totalCount' => $count,
            'db' => Ticket::getDb(),
            'pagination' =>false,
            'sort' => [
                'attributes' => [
                    'ticket',
                    'start',
                    'end',
                    'price',
                ],
            ],
        ]);
        return $this->render('cashier-tickets', [
            'dataProvider'=>$dataProvider,
            'date' => $date,
            'staff' =>$staff ,
        ]);
    }
    
    public function actionRestoreBusSeats($route=null, $hour=null)
    {
        //$tickets = $this->db->createCommand('SELECT * FROM Tickets WHERE route=:route AND dept_date>=curdate() AND dept_time=:time')
        $tickets = $this->db->createCommand('SELECT * FROM Tickets WHERE dept_date>=curdate()')
            //->bindValue(':route', $route)
            //->bindValue(':time', $hour)
            ->queryAll();
        foreach ($tickets as $ticket) {
            //get route paths
            $sql = 'SELECT start, end FROM Routes  WHERE parent = :parent AND  ((idx BETWEEN (SELECT idx FROM Routes  WHERE parent = :parent AND start=:start ORDER BY idx ASC LIMIT 1) AND (SELECT idx FROM Routes  WHERE parent = parent AND end=:end ORDER BY idx ASC LIMIT 1)) OR (idx BETWEEN (SELECT idx FROM Routes  WHERE id = :parent AND start=:start ORDER BY idx ASC LIMIT 1) AND (SELECT idx FROM Routes  WHERE parent = :parent ORDER BY idx DESC LIMIT 1)))';
            $subRoutes =  $this->db->createCommand($sql)
                            ->bindValue(':start', $ticket['start'])
                            ->bindValue(':end', $ticket['end'])
                            ->bindValue(':parent', $ticket['route'])
                            ->queryall();
            
            foreach ($subRoutes as $sub) {
                $this->db->createCommand()->insert('ReservedSeats', [
                    'ticket' => $ticket['id'],
                    'route' => $ticket['route'],
                    'seat' => $ticket['seat'],
                    'dept_date' => $ticket['dept_date'],
                    'dept_time' => $ticket['dept_time'],
                    'expires_in' => $ticket['expired_in'],
                    'status' => $ticket['status'],
                    'bus' => $ticket['bus'],
                    'start' => $sub['start'],
                    'end' => $sub['end'],
                    'issued_on' => time(),
                ])->execute();
            }
        }
    }
    
    /*public function actionRefillUsers()
    {
         //add all routes to the user
        $db = TenantModel::getDb();
        $db->transaction(function($db) {
            $routes = $db->createCommand('SELECT id FROM volcano_rwanda.Routes WHERE parent IS NULL')->queryColumn();
            $users = $db->createCommand('SELECT mobile FROM volcano_shared.Staffs WHERE location="kigali"')->queryColumn();
            foreach($routes as $route)
            {
                foreach($users as $user)
                {
                    $db->createCommand()->insert('volcano_rwanda.SellableRoutes', [
                        'route' => $route,
                        'staff' => $user,
                        'created_by' => Yii::$app->user->id,
                        'updated_by' => Yii::$app->user->id,
                        'created_at' => time(),
                        'updated_at' => time(),
                    ])->execute();
                }
            }
        });
    }*/
    public function beforeAction($action)
    {
        if(!Yii::$app->user->identity->isAdmin())
            throw new ForbiddenHttpException('You are not allowed to access this Page.');
        else
            return parent::beforeAction($action);
    }

}
