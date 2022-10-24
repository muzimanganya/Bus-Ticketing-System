<?php

namespace app\controllers;

use app\models\TicketSearch;
use app\models\Calendar;
use app\models\Route;
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
use DivineOmega\Countries\Countries;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use Yii;

class ReportsController extends TenantController
{

    public function actionBooking($start = null, $end = null, $route = null, $time = null)
    {
        if ($start == null || $end == null)
            $start = $end = date('Y-m-d');

        Yii::$app->formatter->defaultTimeZone = Yii::$app->user->identity->timezone;

        $searchModel = new TicketSearch();
        $dataProvider = $searchModel->searchBooking($route, $start, $time);

        return $this->render('booking', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'date' => $start,
            'time' => $time,
            'route' => $route,
        ]);
    }

    public function actionCustomer()
    {
        $model = new PlannedRoute();


        if ($model->load(Yii::$app->request->post())) {
            $route = Route::find()->where(['id' => $model->route])->one();
            return $this->render('customer-manifesto', [
                'dataProvider' => $model->getTravellers(),
                'model' => $model,
                'route' => $route,
            ]);
        } else {
            return $this->render('customer', [
                'model' => $model
            ]);
        }
    }

    public function actionIndex()
    {
        $model = new \app\models\FindReport();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                //redirect to report with date appended
                return $this->redirect([$model->report, 'start' => $model->start, 'end' => $model->end, 'reference' => $model->reference]);
            }
        }

        return $this->render('index', [
            'model' => $model,
        ]);
    }

    public function actionPromotion($route = null, $start = null, $end = null)
    {
        if ($start == null || $end == null)
            $start = $end = date('Y-m-d');

        $searchModel = new TicketSearch();
        $dataProvider = $searchModel->searchPromo($route, $start, $end);

        return $this->render('promotion', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'date' => "$start to $end",
            'route' => $route,
            'start' => $start,
            'end' => $end,
        ]);
    }

    public function actionMobileTickets($route = null, $start = null, $end = null)
    {
        if ($start == null || $end == null)
            $start = $end = date('Y-m-d');

        $searchModel = new TicketSearch();
        $dataProvider = $searchModel->searchMobile($route, $start, $end);

        return $this->render('mobile-tickets', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'date' => "$start to $end",
            'route' => $route,
            'start' => $start,
            'end' => $end,
        ]);
    }

    public function actionMonthlySales()
    {
        $calendar = new Calendar();

        if (!$calendar->load(Yii::$app->request->post())) {
            $calendar->month = date('m');
            $calendar->year = date('Y');
        }

        $count = $this->db->createCommand('SELECT COUNT(*) FROM (SELECT route  FROM Tickets t WHERE MONTH(FROM_UNIXTIME(t.created_at)) = :month AND YEAR(FROM_UNIXTIME(t.created_at)) = :year GROUP BY t.route) tt', [':month' => $calendar->month, ':year' => $calendar->year])->queryScalar();

        $sql = 'SELECT route, CONCAT(r.start, "-", r.end) AS stops, SUM(CASE WHEN is_promo=0 THEN 1 ELSE 0 end) AS tickets, SUM(CASE WHEN is_promo=1 THEN 1 ELSE 0 end) AS promo, COUNT(DISTINCT machine_serial) AS pos, SUM(CASE WHEN currency="RWF" AND is_promo=0 THEN  price ELSE 0 END) AS RWF, SUM(CASE WHEN currency="FIB" AND is_promo=0 THEN  price ELSE 0 END) AS FIB, SUM(CASE WHEN currency="UGS" AND is_promo=0 THEN  price ELSE 0 END) AS UGS, SUM(CASE WHEN currency="USD" AND is_promo=0 THEN  price ELSE 0 END) AS USD FROM Tickets t INNER JOIN Routes r ON r.id = t.route WHERE MONTH(FROM_UNIXTIME(t.created_at)) = :month AND YEAR(FROM_UNIXTIME(t.created_at)) = :year GROUP BY t.route';

        $dataProvider = new SqlDataProvider([
            'sql' => $sql,
            'db' => $this->db,
            'params' => [':month' => $calendar->month, ':year' => $calendar->year],
            'totalCount' => $count,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('monthly-sales', [
            'calendar' => $calendar,
            'dataProvider' => $dataProvider
        ]);
    }

    public function actionMonthlyRoute($route, $month = null, $year = null)
    {
        $calendar = new Calendar();

        if (!$calendar->load(Yii::$app->request->post())) {
            if (is_null($month))
                $calendar->month = date('m');
            else
                $calendar->month = $month;
            if (is_null($year))
                $calendar->year = date('Y');
            else
                $calendar->year = $year;
        }

        $routeTitle = $this->db->createCommand('SELECT name  FROM Routes WHERE id = :route', [':route' => $route])->queryScalar();
        $count = $this->db->createCommand('SELECT COUNT(*) FROM (SELECT CONCAT(start, "-", end)  FROM Tickets t WHERE MONTH(FROM_UNIXTIME(t.created_at)) = :month AND YEAR(FROM_UNIXTIME(t.created_at)) = :year AND t.route = :route GROUP BY CONCAT(start, "-", end)) tt', [':month' => $calendar->month, ':year' => $calendar->year, ':route' => $route])->queryScalar();


        $sql = 'SELECT route, CONCAT(t.start, "-", t.end) AS stops, SUM(CASE WHEN is_promo=0 THEN 1 ELSE 0 end) AS tickets, SUM(CASE WHEN is_promo=1 THEN 1 ELSE 0 end) AS promo, COUNT(DISTINCT machine_serial) AS pos, SUM(CASE WHEN currency="RWF" AND is_promo=0 THEN  price ELSE 0 END) AS RWF, SUM(CASE WHEN currency="FIB" AND is_promo=0 THEN  price ELSE 0 END) AS FIB, SUM(CASE WHEN currency="UGS" AND is_promo=0 THEN  price ELSE 0 END) AS UGS, SUM(CASE WHEN currency="USD" AND is_promo=0 THEN  price ELSE 0 END) AS USD FROM Tickets t INNER JOIN Routes r ON r.id = t.route WHERE MONTH(FROM_UNIXTIME(t.created_at)) = :month AND YEAR(FROM_UNIXTIME(t.created_at)) = :year AND route = :route GROUP BY CONCAT(start, "-", end) ORDER BY route';

        $dataProvider = new SqlDataProvider([
            'sql' => $sql,
            'db' => $this->db,
            'params' => [':month' => $calendar->month, ':year' => $calendar->year, ':route' => $route],
            'totalCount' => $count,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('monthly-sales-route', [
            'calendar' => $calendar,
            'route' => $routeTitle,
            'dataProvider' => $dataProvider
        ]);
    }

    public function actionSales($route = null, $start = null, $end = null)
    {
        if (is_null($route)) {
            //$route = $this->db->createCommand('SELECT route FROM Tickets WHERE DATE(FROM_UNIXTIME(created_at)) = :date LIMIT 1')
            $route = $this->db->createCommand('SELECT route FROM Tickets WHERE dept_date BETWEEN :start AND :end LIMIT 1')
                ->bindValue(':start', $start)
                ->bindValue(':end', $end)
                ->queryScalar();
        }
        if ($start == null || $end == null)
            $start = $end = date('Y-m-d');

        $searchModel = new TicketSearch();
        $searchModel->load(Yii::$app->request->queryParams);
        $searchModel->route = $route;

        $dataProvider = $searchModel->searchRoutes($start, $end);

        return $this->render('sales', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'date' => "{$start} to {$end}",
            'start' => $start,
            'end' => $end,
            'route' => $route,
        ]);
    }

    public function actionBusDetails($id, $startEnd = null)
    {
        $details = explode(';', base64_decode($id));
        $date = $details[0];
        $route = $details[1];
        $dept_date = $details[2];
        $dept_time = $details[3];
        $bus = $details[4];

        $start = null;
        $end = null;

        if (empty($startEnd)) {
            $ticket = Ticket::find()->where([
                'route' => $route,
                'dept_date' => $dept_date,
                'dept_time' => $dept_time
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
            'route' => $route,
            'dept_date' => $dept_date,
            'dept_time' => $dept_time,
            'start' => $start,
            'end' => $end,
        ];
        $dataProvider = $searchModel->searchBusDetails();

        $dateDept = $dept_date;

        //do show tickets for 3 days ago as today might be missing
        $dept_date = date_create($dept_date);
        date_sub($dept_date, date_interval_create_from_date_string("1 days"));

        return $this->render('bus-details', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'date' => $date,
            'dept_time' => $dept_time,
            'dept_date' => date_format($dept_date, "Y-m-d"),
            'route' => $route,
            'startEnd' => $startEnd,
            'immigrationURL' => [
                'immigration-csv',
                'dept_date' => $dateDept,
                'dept_time' => $dept_time,
                'route' => $route,
                'start' => $start,
                'end' => $end,
                'bus' => $bus,
            ]
        ]);
    }


    public function actionBusTrips($start, $end, $bus)
    {
        $count = $this->db->createCommand('SELECT count(distinct concat(bus,route,dept_date,dept_time)) FROM Tickets WHERE bus=:bus AND dept_date BETWEEN :start AND :end ')
            ->bindValue(':bus', $bus)
            ->bindValue(':start', $start)
            ->bindValue(':end', $end)
            ->queryScalar();


        $sql = 'SELECT concat(r.start,"-",r.end) as troute,t.dept_time,count(t.id) as tickets,SUM(CASE WHEN t.currency= "RWF" THEN t.price-t.discount ELSE 0 END) AS RWF , SUM(CASE WHEN t.currency= "UGS" THEN t.price-t.discount ELSE 0 END) AS UGS, SUM(CASE WHEN t.currency= "FIB" THEN t.price-t.discount ELSE 0 END) AS FIB, SUM(CASE WHEN t.currency= "USD" THEN t.price-t.discount ELSE 0 END) AS USD FROM Tickets t inner join Routes r on t.route=r.id  WHERE t.bus=:bus AND dept_date BETWEEN :start AND :end  group by t.route,t.dept_date,t.dept_time order by t.dept_time';

        $dataProvider = new SqlDataProvider([
            'sql' => $sql,
            'params' => [':bus' => $bus, ':start' => $start, ':end' => $end],
            'totalCount' => $count,
            'db' => Ticket::getDb(),
            'pagination' => false,

        ]);
        return $this->render('bus-trips', [
            'dataProvider' => $dataProvider,
            'date' => "$start to $end",
            'start' => $start,
            'end' => $end,
            'bus' => $bus,
        ]);
    }

    public function actionTrends()
    {
        $searchModel = new TicketSearch();

        $weeklySales = $searchModel->searchWeeklySales();
        $bestSellers = $searchModel->searchBestSellers();
        $bestRoutes = $searchModel->searchBestRoutes();

        return $this->render('trends', [
            'weekly' => $weeklySales,
            'bestSellers' => $bestSellers,
            'bestRoutes' => $bestRoutes,
        ]);
    }

    public function actionUserSales($start, $end)
    {
        $searchModel = new TicketSearch();
        $dataProvider = $searchModel->searchUserSales($start, $end);

        return $this->render('user-report', [
            'dataProvider' => $dataProvider,
            'date' => "$start to $end",
            'start' => $start,
            'end' => $end,
        ]);
    }

    public function actionUserSalesDetails($start, $end, $user)
    {
        $count = $this->db->createCommand('SELECT COUNT(ticket) FROM Tickets WHERE created_by=:author AND DATE(FROM_UNIXTIME(updated_at)) BETWEEN :start AND :end AND status<>"BO" GROUP BY machine_serial')
            ->bindValue(':author', $user)
            ->bindValue(':start', $start)
            ->bindValue(':end', $end)
            ->queryScalar();

        $dataProvider = new SqlDataProvider([
            'sql' => 'SELECT machine_serial AS pos, SUM(CASE WHEN t.currency= "RWF" THEN price-discount ELSE 0 END) AS RWF , SUM(CASE WHEN t.currency= "UGS" THEN price-discount ELSE 0 END) AS UGS, SUM(CASE WHEN t.currency= "FIB" THEN price-discount ELSE 0 END) AS FIB, SUM(CASE WHEN t.currency= "USD" THEN price-discount ELSE 0 END) AS USD FROM Tickets t WHERE created_by=:author AND DATE(FROM_UNIXTIME(updated_at)) BETWEEN :start AND :end  AND status<>"BO" GROUP BY machine_serial',
            'params' => [':author' => $user, ':start' => $start, ':end' => $end],
            'totalCount' => $count,
            'db' => Ticket::getDb(),
            'pagination' => false,
            'sort' => [
                'attributes' => [
                    'pos',
                ],
            ],
        ]);
        return $this->render('user-report-details', [
            'dataProvider' => $dataProvider,
            'date' => "$start to $end",
            'name' => $user,
        ]);
    }


    public function actionPosSalesDetails($start, $end, $pos)
    {
        $count = $this->db->createCommand('SELECT COUNT(ticket) FROM Tickets WHERE machine_serial=:pos AND DATE(FROM_UNIXTIME(updated_at)) BETWEEN :start AND :end GROUP BY updated_by')
            ->bindValue(':pos', $pos)
            ->bindValue(':start', $start)
            ->bindValue(':end', $end)
            ->queryScalar();

        $dataProvider = new SqlDataProvider([
            'sql' => 'SELECT s.name AS author, COUNT(t.ticket) AS tickets, SUM(CASE WHEN t.currency= "RWF" THEN price-discount ELSE 0 END) AS RWF , SUM(CASE WHEN t.currency= "UGS" THEN price-discount ELSE 0 END) AS UGS, SUM(CASE WHEN t.currency= "FIB" THEN price-discount ELSE 0 END) AS FIB, SUM(CASE WHEN t.currency= "USD" THEN price-discount ELSE 0 END) AS USD FROM Tickets t  INNER JOIN volcano_shared.Staffs s ON s.mobile = t.updated_by WHERE machine_serial=:pos AND DATE(FROM_UNIXTIME(t.updated_at)) BETWEEN :start AND :end AND status=\'CO\' GROUP BY t.updated_by, s.name',
            'params' => [':pos' => $pos, ':start' => $start, ':end' => $end],
            'totalCount' => $count,
            'db' => Ticket::getDb(),
            'pagination' => false,
            'sort' => [
                'attributes' => [
                    'pos',
                ],
            ],
        ]);
        return $this->render('pos-report-details', [
            'dataProvider' => $dataProvider,
            'date' => "$start to $end",
            'start' => $start,
            'end' => $end,
            'pos' => $pos,
        ]);
    }

    public function actionPosSales($start, $end)
    {
        $searchModel = new TicketSearch();
        $dataProvider = $searchModel->searchPosSales($start, $end);

        return $this->render('pos-report', [
            'dataProvider' => $dataProvider,
            'date' => "$start to $end",
            'start' => $start,
            'end' => $end,
        ]);
    }

    public function actionCardSales($start, $end, $reference = null)
    {
        $searchModel = new RouteCardSearch();
        $dataProvider = $searchModel->searchDailySales($start, $end, $reference);
        $total = 0;
        foreach ($dataProvider->getModels() as $model) {
            $total = $total + ($model->price * $model->total_trips);
        }

        return $this->render('card-sales', [
            'dataProvider' => $dataProvider,
            'total' => $total,
            'date' => "$start to $end",
            'start' => $start,
            'end' => $end,
            'mobile' => $reference,
        ]);
    }

    public function actionPlanned($start = null, $end = null)
    {
        if ($start == null || $end == null)
            $start = $end = date('Y-m-d');

        $searchModel = new PlannedRouteSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $start, $end);

        return $this->render('planned-routes', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionLogs($start = null, $end = null)
    {
        if ($start == null || $end == null)
            $start = $end = date('Y-m-d');

        $searchModel = new SysLogSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $start, $end);

        return $this->render('logs', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }



    public function actionImmigrationCsv($dept_date, $dept_time, $route, $bus, $start, $end, $download = 0)
    {
        if ($start == null)
            $start = date('Y-m-d');

        if ($end == null)
            $end = date('Y-m-d');

        if (empty($bus)) {
            Yii::$app->session->setFlash('danger', 'You must pass bus number as a reference to Immigration CSV report');
            return $this->redirect(['reports/index']);
        }


        $searchModel = new TicketSearch();

        $searchModel->attributes = [
            'route' => $route,
            'dept_date' => $dept_date,
            'dept_time' => $dept_time,
            'start' => $start,
            'end' => $end,
        ];
        $dataProvider = $searchModel->searchBusDetails();

        if ($download == 1) {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            //set headings
            $sheet->setCellValueByColumnAndRow(1, 1, 'First Name');
            $sheet->setCellValueByColumnAndRow(2, 1, 'Surname');
            $sheet->setCellValueByColumnAndRow(3, 1, 'Other Name(s)');
            $sheet->setCellValueByColumnAndRow(4, 1, 'Nationality');
            $sheet->setCellValueByColumnAndRow(5, 1, 'Gender');
            $sheet->setCellValueByColumnAndRow(6, 1, ' Date of Birth');
            $sheet->setCellValueByColumnAndRow(7, 1, 'Traveller Type');
            $sheet->setCellValueByColumnAndRow(8, 1, 'Travel Doc Country');
            $sheet->setCellValueByColumnAndRow(9, 1, 'Travel Doc Type');
            $sheet->setCellValueByColumnAndRow(10, 1, 'Travel Doc Number');
            $sheet->setCellValueByColumnAndRow(11, 1, 'Doc Expiry Date');
            $sheet->setCellValueByColumnAndRow(12, 1, 'Country of Embarkation');
            $sheet->setCellValueByColumnAndRow(13, 1, 'Country of DisEmbarkation');
            $sheet->setCellValueByColumnAndRow(14, 1, 'Port of Embarkation');
            $sheet->setCellValueByColumnAndRow(15, 1, 'Port of DisEmbarkation');
            $sheet->setCellValueByColumnAndRow(16, 1, 'Booking Reference');
            $sheet->setCellValueByColumnAndRow(17, 1, 'Seat Number');
            $sheet->setCellValueByColumnAndRow(18, 1, 'Checked Bags');
            $sheet->setCellValueByColumnAndRow(19, 1, 'Bag Tag(s)');



            //add data
            $i = 1;
            $countries = new Countries();
            foreach ($dataProvider->getModels() as $model) {
                $customer = $model->customerR;
                $name = empty($customer) ? ['', '', ''] : explode(' ', str_replace('  ', ' ', $customer->name));
                $sheet->setCellValueByColumnAndRow(1, $i + 1, count($name) > 0 ? $name[0] : '');
                $sheet->setCellValueByColumnAndRow(2, $i + 1, count($name) > 1 ? $name[1] : '');
                $sheet->setCellValueByColumnAndRow(3, $i + 1, count($name) > 2 ? $name[2] : '');
                $sheet->setCellValueByColumnAndRow(4, $i + 1, $model->customerR->nationality ?? '');
                $sheet->setCellValueByColumnAndRow(5, $i + 1, $model->customerR->genderShortName ?? '');
               // $sheet->setCellValueByColumnAndRow(6, $i + 1, $model->customerR->dob ?? '');
                $sheet->setCellValueByColumnAndRow(6, $i + 1, '');
                $sheet->setCellValueByColumnAndRow(7, $i + 1, $model->travellerType ?? '');
                $sheet->setCellValueByColumnAndRow(8, $i + 1, $model->doc_country == null ? '' : $countries->getByIsoCode($model->doc_country)->name);
                $sheet->setCellValueByColumnAndRow(9, $i + 1, $model->documentType ?? '');
                $sheet->setCellValueByColumnAndRow(10, $i + 1, $model->doc_number);
                //$sheet->setCellValueByColumnAndRow(11, $i + 1, $model->doc_expiry ?? '');
                $sheet->setCellValueByColumnAndRow(11, $i + 1, '');
                $sheet->setCellValueByColumnAndRow(12, $i + 1, $model->from_country == null ? '' : $countries->getByIsoCode($model->from_country)->name);
                $sheet->setCellValueByColumnAndRow(13, $i + 1, $model->to_country == null ? '' : $countries->getByIsoCode($model->to_country)->name);
                $sheet->setCellValueByColumnAndRow(14, $i + 1, $model->start);
                $sheet->setCellValueByColumnAndRow(15, $i + 1, $model->end);
                $sheet->setCellValueByColumnAndRow(16, $i + 1, $model->ticket);
                $sheet->setCellValueByColumnAndRow(17, $i + 1, $model->seat);
                $sheet->setCellValueByColumnAndRow(18, $i + 1, $model->number_of_bags);
                $sheet->setCellValueByColumnAndRow(19, $i + 1, $model->bag_tags);


                $i++;
            }

            $fileName = 'Immigration-Export-' . date('Y-m-d') . '.csv';
            $writer = new Csv($spreadsheet);
            $writer->setEnclosure('');

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
            $writer->save('php://output');
            exit(0);
        }

        return $this->render('immigration-csv', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'dept_time' => $dept_time,
            'dept_date' => date_format(date_create($dept_date), "Y-m-d"),
            'route' => $route,
            'startEnd' => "$start - $end",
            'downloadURL' => [
                'immigration-csv',
                'dept_date' => date_format(date_create($dept_date), "Y-m-d"),
                'dept_time' => $dept_time,
                'route' => $route,
                'start' => $start,
                'end' => $end,
                'bus' => $bus,
                'download' => 1
            ]
        ]);
    }


    public function actionFindLogTicket()
    {
        $ticket = Yii::$app->request->post('ticket');
        $model = Ticket::find()->where(['ticket' => $ticket])->one();

        $searchModel = new SysLogSearch();
        if ($model) {
            $model->reference = $model->id;
        }

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('logs', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCustomerTickets($start, $end, $reference)
    {
        $customer = Customer::findOne($reference);
        if (empty($customer)) {
            throw new NotFoundHttpException("Customer Not Found. Please Check if Reference $reference is Correct");
        }

        $count = $this->db->createCommand('SELECT COUNT(ticket) FROM Tickets WHERE customer=:customer AND DATE(FROM_UNIXTIME(created_at)) BETWEEN :start AND :end AND (status="CO" OR status="CT")')
            ->bindValue(':customer', $reference)
            ->bindValue(':start', $start)
            ->bindValue(':end', $end)
            ->queryScalar();

        $dataProvider = new SqlDataProvider([
            'sql' => 'SELECT t.*, s.name, s.mobile FROM Tickets t INNER JOIN volcano_shared.Staffs s ON s.mobile = t.created_by WHERE customer=:customer AND DATE(FROM_UNIXTIME(t.created_at)) BETWEEN :start AND :end AND (status="CO" OR status="CT") ',
            'params' => [':customer' => $reference, ':start' => $start, ':end' => $end],
            'totalCount' => $count,
            'db' => Ticket::getDb(),
            'pagination' => false,
            'sort' => [
                'attributes' => [
                    'ticket',
                    'start',
                    'end',
                    'price',
                ],
            ],
        ]);

        return $this->render('customer-tickets', [
            'dataProvider' => $dataProvider,
            'date' => "$start to $end",
            'start' => $start,
            'end' => $end,
            'customer' => $customer,
        ]);
    }


    public function actionCashierTickets($start, $end, $reference)
    {
        $staff = Staff::findOne($reference);
        if (empty($staff)) {
            throw new NotFoundHttpException("Cashier Not Found. Please Check if Reference $reference is Correct");
        }

        $count = $this->db->createCommand('SELECT COUNT(ticket) FROM Tickets WHERE status="CO" AND  updated_by=:staff AND DATE(FROM_UNIXTIME(updated_at)) BETWEEN :start AND :end')
            ->bindValue(':staff', $reference)
            ->bindValue(':start', $start)
            ->bindValue(':end', $end)
            ->queryScalar();

        $dataProvider = new SqlDataProvider([
            'sql' => 'SELECT t.*, s.name, s.mobile FROM Tickets t INNER JOIN volcano_shared.Staffs s ON s.mobile = t.updated_by WHERE t.updated_by=:staff AND t.status="CO" and DATE(FROM_UNIXTIME(t.updated_at)) BETWEEN :start AND :end',
            'params' => [':staff' => $reference, ':start' => $start, ':end' => $end],
            'totalCount' => $count,
            'db' => Ticket::getDb(),
            'pagination' => false,
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
            'dataProvider' => $dataProvider,
            'date' => "{$start} - {$end}",
            'staff' => $staff,
        ]);
    }

    public function actionRestoreBusSeats($route = null, $hour = null)
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

    /*public function actionBusReport($start=null, $end=null, $reference=null)
    {
        $bus = $reference;
        $count = 0;
        $sql = '';
        $params = [];
        
        if($start==null || $end == null)
            $start = $end = date('Y-m-d');
        
        if($reference==null)
        {
            $count = $this->db->createCommand('SELECT COUNT(*) FROM (SELECT bus FROM Tickets  WHERE dept_date BETWEEN :start AND :end GROUP BY bus) t ', [':start' => $start, ':end'=>$end])->queryScalar();
            
            $sql = 'SELECT bus, COUNT(ticket) AS tickets, SUM( price) AS RWF, SUM(CASE WHEN currency="FIB"  THEN  price-discount ELSE 0 END) AS FIB, SUM(CASE WHEN currency="UGS"  THEN  price-discount ELSE 0 END) AS UGS, SUM(CASE WHEN currency="USD"  THEN  price-discount ELSE 0 END) AS USD FROM Tickets WHERE dept_date BETWEEN :start AND :end GROUP BY bus';
            
            $params = [':start' => $start, ':end'=>$end];
        }
        else
        {
            $count = $this->db->createCommand('SELECT COUNT(*) FROM (SELECT bus FROM Tickets WHERE bus = :bus AND DATE(FROM_UNIXTIME(updated_at)) BETWEEN :start AND :end GROUP BY bus) t ', [':start' => $start, ':end'=>$end, 'bus'=>$bus])->queryScalar();
            
            $sql = 'SELECT bus, COUNT(ticket) AS tickets, SUM(CASE WHEN currency="RWF"  THEN  price-discount ELSE 0 END) AS RWF, SUM(CASE WHEN currency="FIB"  THEN  price-discount ELSE 0 END) AS FIB, SUM(CASE WHEN currency="UGS"  THEN  price-discount ELSE 0 END) AS UGS, SUM(CASE WHEN currency="USD"  THEN  price-discount ELSE 0 END) AS USD FROM Tickets WHERE bus = :bus AND DATE(FROM_UNIXTIME(updated_at)) BETWEEN :start AND :end GROUP BY bus';
            
            $params = [':start' => $start, ':end'=>$end, 'bus'=>$bus];
        }
        
        $dataProvider = new SqlDataProvider([
            'sql' => $sql,
            'db'=>$this->db,
            'params' => $params,
            'totalCount' => $count,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        
        return $this->render('bus-report', [
            'dataProvider'=>$dataProvider,
            'date' => "$start to $end", 
            'start'=>$start,
            'end'=>$end,
            'bus'=>$bus,
        ]);
    }*/


    public function actionBusReport($start = null, $end = null, $reference = null)
    {
        $bus = $reference;
        $count = 0;
        $sql = '';
        $params = [];

        if ($start == null || $end == null)
            $start = $end = date('Y-m-d');

        if ($reference == null) {
            $count = $this->db->createCommand('SELECT COUNT(*) FROM (SELECT bus FROM Tickets  WHERE DATE(FROM_UNIXTIME(updated_at)) BETWEEN :start AND :end GROUP BY bus) t ', [':start' => $start, ':end' => $end])->queryScalar();

            $sql = 'SELECT bus, COUNT(CASE WHEN discount=0 THEN ticket end) AS tickets, SUM(CASE WHEN currency="RWF"  THEN  price-discount ELSE 0 END) AS RWF, SUM(CASE WHEN currency="FIB"  THEN  price-discount ELSE 0 END) AS FIB, SUM(CASE WHEN currency="UGS"  THEN  price-discount ELSE 0 END) AS UGS, SUM(CASE WHEN currency="USD"  THEN  price-discount ELSE 0 END) AS USD FROM Tickets WHERE  dept_date BETWEEN :start AND :end GROUP BY bus';

            $params = [':start' => $start, ':end' => $end];
        } else {
            $count = $this->db->createCommand('SELECT COUNT(*) FROM (SELECT bus FROM Tickets WHERE bus = :bus AND DATE(FROM_UNIXTIME(updated_at)) BETWEEN :start AND :end GROUP BY bus) t ', [':start' => $start, ':end' => $end, 'bus' => $bus])->queryScalar();

            $sql = 'SELECT bus, COUNT(ticket) AS tickets, SUM(CASE WHEN currency="RWF"  THEN  price-discount ELSE 0 END) AS RWF, SUM(CASE WHEN currency="FIB"  THEN  price-discount ELSE 0 END) AS FIB, SUM(CASE WHEN currency="UGS"  THEN  price-discount ELSE 0 END) AS UGS, SUM(CASE WHEN currency="USD"  THEN  price-discount ELSE 0 END) AS USD FROM Tickets WHERE bus = :bus AND dept_date BETWEEN :start AND :end GROUP BY bus';

            $params = [':start' => $start, ':end' => $end, 'bus' => $bus];
        }

        $dataProvider = new SqlDataProvider([
            'sql' => $sql,
            'db' => $this->db,
            'params' => $params,
            'totalCount' => $count,
            'pagination' => [
                'pageSize' => 200,
            ],
        ]);

        return $this->render('bus-report', [
            'dataProvider' => $dataProvider,
            'date' => "$start to $end",
            'start' => $start,
            'end' => $end,
            'bus' => $bus,
        ]);
    }



    public function beforeAction($action)
    {
        if (Yii::$app->user->identity->isMobile()) {
            return parent::beforeAction($action);
        }
        if (!Yii::$app->user->identity->isAdmin())
            throw new ForbiddenHttpException('You are not allowed to access this Page.');
        else
            return parent::beforeAction($action);
    }
}
