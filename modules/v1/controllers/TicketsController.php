<?php

namespace app\modules\v1\controllers;

use yii\rest\ActiveController;
use app\models\RouteCard;
use app\models\TenantModel;
use app\models\Wallet;
use yii\db\Query;
use app\models\Bus;
use app\models\Route;
use app\models\Stop;
use app\models\POS;
use app\models\Customer;
use app\models\PlannedRoute;
use app\models\Ticket;
use app\models\Point;
use Exception;
use GuzzleHttp\Client;
use PDO;
use yii\helpers\ArrayHelper;
use Yii;

use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\web\ServerErrorHttpException;

/**
 * User Mgt controller for the `v1` module
 */
class TicketsController extends ActiveController
{
    public $modelClass = 'app\models\Tickets';

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

    public function actionStops($beautify = 0)
    {
        $ret = [];
        if ($beautify == 1) {
            $sql = 'SELECT r.start, p.is_intl FROM Routes r  INNER JOIN Routes p ON r.parent = p.id OR r.id=p.id WHERE  r.id IN( SELECT route FROM  SellableRoutes WHERE staff = :mobile) OR r.parent IN( SELECT route FROM  SellableRoutes WHERE staff = :mobile) UNION SELECT r.end, p.is_intl FROM Routes r  INNER JOIN Routes p ON r.parent = p.id OR r.id = p.id WHERE  r.id IN( SELECT route FROM  SellableRoutes WHERE staff = :mobile) OR r.parent IN( SELECT route FROM  SellableRoutes WHERE staff = :mobile)';
            $stops = $this->db->createCommand($sql)
                ->bindValue('mobile', Yii::$app->user->id)
                ->queryAll();
            //get routes
            $routes = $this->db->createCommand('SELECT g.id, g.name FROM Routes AS g INNER JOIN Routes r ON g.return = r.id WHERE g.id IN( SELECT route FROM  SellableRoutes WHERE staff = :mobile)')
                ->bindValue('mobile', Yii::$app->user->id)
                ->queryAll();

            $ret['routes'] = $routes;
            $ret['stops'] = $stops;
        } else {
            $sql = 'SELECT r.start, p.is_intl FROM Routes r  INNER JOIN Routes p ON r.parent = p.id OR r.id=p.id WHERE  r.id IN( SELECT route FROM  SellableRoutes WHERE staff = :mobile) OR r.parent IN( SELECT route FROM  SellableRoutes WHERE staff = :mobile) UNION SELECT r.end, p.is_intl FROM Routes r  INNER JOIN Routes p ON r.parent = p.id OR r.id = p.id WHERE  r.id IN( SELECT route FROM  SellableRoutes WHERE staff = :mobile) OR r.parent IN( SELECT route FROM  SellableRoutes WHERE staff = :mobile)';
            $ret = $this->db->createCommand($sql)
                ->bindValue('mobile', Yii::$app->user->id)
                ->queryAll();
            //get routes
            $routes = $this->db->createCommand('SELECT g.id, g.name FROM Routes AS g INNER JOIN Routes r ON g.return = r.id WHERE g.id IN( SELECT route FROM  SellableRoutes WHERE staff = :mobile)')
                ->bindValue('mobile', Yii::$app->user->id)
                ->queryAll();

            $ret['routes'] = $routes;
        }

        return $ret;
    }

    public function actionStopsv2()
    {
        $sql = 'SELECT r.start, p.is_intl FROM Routes r  INNER JOIN Routes p ON r.parent = p.id OR r.id=p.id WHERE  r.id IN( SELECT route FROM  SellableRoutes WHERE staff = :mobile) OR r.parent IN( SELECT route FROM  SellableRoutes WHERE staff = :mobile) UNION SELECT r.end, p.is_intl FROM Routes r  INNER JOIN Routes p ON r.parent = p.id OR r.id = p.id WHERE  r.id IN( SELECT route FROM  SellableRoutes WHERE staff = :mobile) OR r.parent IN( SELECT route FROM  SellableRoutes WHERE staff = :mobile)';
        $stops = $this->db->createCommand($sql)
            ->bindValue('mobile', Yii::$app->user->id)
            ->queryAll();
        //get routes
        $sql = 'SELECT g.id, CONCAT(g.start, "-", g.end, " ", g.name) as name, g.is_intl FROM Routes AS g INNER JOIN Routes r ON g.return = r.id WHERE g.id IN( SELECT route FROM  SellableRoutes WHERE staff = :mobile)';
        $routes = $this->db->createCommand($sql)
            ->bindValue('mobile', Yii::$app->user->id)
            ->queryAll();
        if (empty($routes)) {
            $sql = 'SELECT re.id, CONCAT(re.start, "-", re.end, " ", re.name) as name, re.is_intl FROM Routes AS re INNER JOIN Routes ro ON ro.id = re.id WHERE re.id IN( SELECT route FROM  SellableRoutes WHERE staff = :mobile)';
            $routes = $this->db->createCommand($sql)
                ->bindValue('mobile', Yii::$app->user->id)
                ->queryAll();
        }

        $results = [];
        $results['routes'] = $routes;
        $results['stops'] = $stops;

        return $results;
    }

    public function actionStopsFullRoutes()
    {
        $sql = 'SELECT r.start, p.is_intl FROM Routes r  INNER JOIN Routes p ON r.parent = p.id OR r.id=p.id WHERE  r.id IN( SELECT route FROM  SellableRoutes WHERE staff = :mobile) OR r.parent IN( SELECT route FROM  SellableRoutes WHERE staff = :mobile) UNION SELECT r.end, p.is_intl FROM Routes r  INNER JOIN Routes p ON r.parent = p.id OR r.id = p.id WHERE  r.id IN( SELECT route FROM  SellableRoutes WHERE staff = :mobile) OR r.parent IN( SELECT route FROM  SellableRoutes WHERE staff = :mobile)';
        $stops = $this->db->createCommand($sql)
            ->bindValue('mobile', Yii::$app->user->id)
            ->queryAll();
        //get routes
        $sql = 'SELECT DISTINCT id, name, is_intl FROM
                (
                    SELECT g.id, CONCAT(g.start, "-", g.end, " ", g.name) as name, g.is_intl FROM Routes AS g INNER JOIN Routes r ON g.return = r.id WHERE g.id IN( SELECT route FROM  SellableRoutes WHERE staff = :mobile)
                    UNION
                    SELECT r.id, CONCAT(r.start, "-", r.end, " ", r.name) as name, r.is_intl FROM Routes AS r INNER JOIN Routes rr ON r.id = rr.id WHERE r.id IN( SELECT route FROM  SellableRoutes WHERE staff = :mobile)
                ) t ORDER BY id';
        $routes = $this->db->createCommand($sql)
            ->bindValue('mobile', Yii::$app->user->id)
            ->queryAll();
        if (empty($routes)) {
            $sql = 'SELECT re.id, CONCAT(re.start, "-", re.end, " ", re.name) as name, re.is_intl FROM Routes AS re INNER JOIN Routes ro ON ro.id = re.id WHERE re.id IN( SELECT route FROM  SellableRoutes WHERE staff = :mobile)';
            $routes = $this->db->createCommand($sql)
                ->bindValue('mobile', Yii::$app->user->id)
                ->queryAll();
        }

        $results = [];
        $results['routes'] = $routes;
        $results['stops'] = $stops;

        return $results;
    }




    public function actionPayBooking($id, $pos)
    {
        $success = [];
        $msg = $this->isAllowed($pos);
        if (is_array($msg)) {
            return $msg;
        }

        $booking = Ticket::find()->where(['ticket' => $id])->one();
        if (empty($booking)) {
            $success['success'] = false;
            $success['message'] = 'Booking does not exist!';
        } elseif ($booking->status == Ticket::STATUS_CONFIRMED) {
            if ($booking->is_printed == 0) {
                $success['success'] = true;
                $success['message'] = $this->formatTicket($booking);
                Ticket::updateAll(['is_printed' => 1], ['id' => $booking->id]);
            } else {
                $success['success'] = false;
                $success['message'] = 'Booking already sold!';
            }
        } else {
            $dateStr = $booking->dept_date . ' ' . str_replace('H', ':', $booking->dept_time);
            $date = new \DateTime($dateStr, new \DateTimeZone(Yii::$app->user->identity->timezone));
            $deptTimeUnix = $date->format('U');

            $expiryTime = $deptTimeUnix - $booking->expired_in;


            if ($expiryTime < time()) {
                $success['success'] = false;
                $success['message'] = 'Booking have expired!';
            } else {
                //check bus is not locked

                if ($booking->proute->is_active == 0) {
                    $success['success'] = false;
                    $success['message'] = 'Bus is Locked cannot sell';
                    return $success;
                }

                //check if it is mobile and set the QR code algo
                $isMobile = false;
                $wallet = null;
                if ($booking->status == Ticket::STATUS_TEMPORARY_BOOKED) {
                    $isMobile = true;
                    $quota = 100000;
                    //deduct money
                    if (Yii::$app->user->id <> 110001) {
                        $wallet = Wallet::find()->where(['owner' => Yii::$app->user->id])->one();
                        if (empty($wallet)) {
                            $success['success'] = false;
                            $success['message'] = 'Invalid or Suspended Wallet';
                            return $success;
                        } elseif ($wallet->currency != $booking->currency) {
                            $success['success'] = false;
                            $success['message'] = 'Wallet does not support the currency';
                            return $success;
                        } elseif ($wallet->current_amount < $booking->price) {
                            $success['success'] = false;
                            $success['message'] = 'Wallet does not have enough balance';
                            return $success;
                        } else {
                            //check for quota and send message
                            if ($wallet->current_amount < $quota) {
                                $variables = [
                                    'b' => number_format($wallet->current_amount),
                                    'l' => number_format($wallet->last_recharged),
                                    'c' => $wallet->currency,
                                    'm' => number_format($quota),
                                ];
                                Yii::$app->mailer->compose()
                                    ->setFrom('wallet@lapexpress.co.rw')
                                    ->setTo('vendiapp2018@gmail.com')
                                    ->setCc('habdes1@gmail.com')
                                    ->setSubject('Wallet Balance is Low')
                                    ->setTextBody(Yii::t('app', 'Hi,This is to inform you that your balance is low (below {m} {c}). Your current balance is {b} {c}. Your last Top up was {l} {c}. \nThank You,\nLap Express Ltd', $variables))
                                    ->setHtmlBody(Yii::t('app', '<p>Hi</p>,<p>This is to inform you that your balance is low (below {m} {c}).Your current balance is {b} {c}. Your last Top up was {l} {c}.</p><br>Thank You,<br>Lap Express Ltd', $variables))
                                    ->send();
                            }
                        }
                    }
                }


                $booking->status = Ticket::STATUS_CONFIRMED;

                //update Ticket field and send results
                $factory = new \RandomLib\Factory;
                $generator = $factory->getGenerator(new \SecurityLib\Strength(\SecurityLib\Strength::MEDIUM));
                $booking->ticket = $generator->generateString(12, 'ABCDEFGHIJKL09876MNOPQRSTUVWXYZ54321');

                $booking->updated_by = Yii::$app->user->id;
                $booking->updated_at = time();
                $booking->expired_in = 0;
                $booking->machine_serial = $pos;



                if ($booking->routeR->has_promotion == 1) {
                    $pstart = $booking->start;
                    $pend = $booking->end;
                    if ($booking->routeR->is_return == 1) {
                        //to make life easier in handling points all return routes
                        //are converted to going route equivalent and then inserted that way
                        //GITEGA BUJA will be added as BUJA GITEGA
                        $pstart = $booking->end;
                        $pend = $booking->start;
                    }
                    $pts = Point::findOne(['customer' => $booking->customer, 'start' => $pstart, 'end' => $pend]);
                    if (empty($pts)) {
                        $pts = new Point;
                        $pts->attributes = [
                            'customer' => $booking->customer,
                            'start' => $pstart,
                            'end' => $pend,
                            'points' => 1,
                            'created_at' => time(),
                            'updated_at' => time(),
                        ];
                        $pts->save();
                    } else {
                        $pts->points = $pts->points + 1;
                        $pts->updated_at = time();
                        $pts->save(false);
                    }
                    //check if it is promotion
                    $promo = Yii::$app->settings->get('ticket', 'promotion');
                    if ($pts->points > $promo) {
                        $booking->is_promo = 1;
                        $booking->discount = $booking->price;
                        $pts->points = 0;
                        $pts->save(false);
                    }
                }

                if ($booking->save(false)) {
                    $success['success'] = true;
                    $success['message'] = $this->formatTicket($booking);

                    if ($isMobile) {
                        //generate QR code = hash(created_at.0.created_by.0.id)
                        $success['message']['qrcode'] = hash('SHA256', $booking->created_at . '0' . $booking->created_by . '0' . $booking->id);


                        //add Yii::$app->user->id<>110001 to ignore lumicash wallet







                        if ($booking->is_promo == 0 && Yii::$app->user->id <> 110001) {
                            $wallet->current_amount = $wallet->current_amount - $booking->price;
                            if (!$wallet->save(false)) {
                                Yii::error('ticket', json_encode(['errors' => $wallet->errors, 'ticket' => $booking->attributes]));
                            }
                        }
                        //check if it is below Acceptable balance
                    }

                    //update ReservedSeats
                    $this->db->createCommand()->update(
                        'ReservedSeats',
                        [
                            'status' => Ticket::STATUS_CONFIRMED,
                        ],
                        ['ticket' => $booking->id]
                    )->execute();

                    if ($booking->routeR->send_sms == 1) {
                        $customerNumber = $booking->customer;
                        /*if ($booking->start=='KAMPALA') {
                            if (substr($customerNumber, 0, 1)=='0') {//number starts with 0
                                $customerNumber = substr($customerNumber, 1);
                            }
                            //if number does not start with 256
                            if (substr($customerNumber, 0, 3)!='256' && substr($customerNumber, 0, 4)!='+256') {
                                $customerNumber = '256'.$customerNumber;
                            }
                        }*/
                        //1hr before boarding
                        //change hours to minutes
                        $timeExp = explode('H', $booking->dept_time);
                        $totalMin = ($timeExp[0] * 60) + $timeExp[1];
                        $totalMinDiff = $totalMin - 60;
                        //chenge back to 13H00 Format
                        $hr = intval($totalMinDiff / 60);
                        $min = $totalMinDiff % 60;
                        $time = sprintf("%02dH%02d", $hr, $min);

                        //save SMS ready for sending
                        /*$this->db->createCommand()->insert('SMS', [
                            'ticket' => $booking->id,
                            'customer' => $customerNumber,
                            'route' => $booking->route,
                            'dept_date' => $booking->dept_date,
                            'dept_time' => $booking->dept_time,
                            'message' => "Hello! Ticket:{$booking->ticket}, {$booking->start}-{$booking->end} Seat:{$booking->seat}, {$booking->dept_date},{$booking->dept_time}. Inquiries call {$booking->routeR->customer_care}. Thanks for choosing VOLCANO EXPRESS",
                            'created_at' => time(),
                            'updated_at' => time(),
                            'created_by' => Yii::$app->user->id,
                            'updated_by' => Yii::$app->user->id,
                        ])->execute();*/
                    }
                } else {
                    $success['success'] = false;
                    $success['message'] = 'Ticketing failed. Try again!';
                }
            }
        }
        return $success;
    }

    public function actionPayBookingv2($id, $pos)
    {
        $result = Yii::$app->runAction('/v1/tickets/pay-booking', ['id' => $id, 'pos' => $pos]);
        if ($result['success']) {
            $result['data'] = $result['message'];
            $result['message'] = '';
        }
        return $result;
    }

    public function actionPayBookingWithSms($id, $pos, $name, $mobile)
    {
        $success = [];
        $msg = $this->isAllowed($pos);
        if (is_array($msg)) {
            return $msg;
        }

        //Insert User if not existing
        try {
            $sql = 'REPLACE INTO Customers(mobile,name, created_at, updated_at, created_by, updated_by) VALUES(:mobile, :name, :created, :updated, :author, :editor);';
            $this->db->createCommand($sql)->bindValues([
                ':name' => $name,
                ':mobile' => $mobile,
                ':created' => time(),
                ':updated' => time(),
                ':author' => Yii::$app->user->id,
                ':editor' => Yii::$app->user->id,
            ])->execute();
        } catch (\yii\base\Exception $e) {
            //number exists so silently ignore this...!
        }

        $booking = Ticket::find()->where(['ticket' => $id])->one();
        if (empty($booking)) {
            $success['success'] = false;
            $success['message'] = 'Booking does not exist!';
        } elseif ($booking->status == Ticket::STATUS_CONFIRMED) {
            $success['success'] = false;
            $success['message'] = 'Booking already sold!';
        } else {
            $booking->customer = $mobile; //Update one who booked

            $dateStr = $booking->dept_date . ' ' . str_replace('H', ':', $booking->dept_time);
            $date = new \DateTime($dateStr, new \DateTimeZone(Yii::$app->user->identity->timezone));
            $deptTimeUnix = $date->format('U');

            $expiryTime = $deptTimeUnix - $booking->expired_in;


            if ($expiryTime < time()) {
                $success['success'] = false;
                $success['message'] = 'Booking have expired!';
            } else {
                //check bus is not locked

                if ($booking->proute->is_active == 0) {
                    $success['success'] = false;
                    $success['message'] = 'Bus is Locked cannot sell';
                    return $success;
                }

                $booking->status = Ticket::STATUS_CONFIRMED;

                //update Ticket field and send results
                $factory = new \RandomLib\Factory;
                $generator = $factory->getGenerator(new \SecurityLib\Strength(\SecurityLib\Strength::MEDIUM));
                $booking->ticket = $generator->generateString(12, 'ABCDEFGHIJKL09876MNOPQRSTUVWXYZ54321');

                $booking->updated_by = Yii::$app->user->id;
                $booking->updated_at = time();
                $booking->expired_in = 0;
                $booking->machine_serial = $pos;

                if ($booking->routeR->has_promotion == 1) {
                    $pstart = $booking->start;
                    $pend = $booking->end;
                    if ($booking->routeR->is_return == 1) {
                        //to make life easier in handling points all return routes
                        //are converted to going route equivalent and then inserted that way
                        //GITEGA BUJA will be added as BUJA GITEGA
                        $pstart = $booking->end;
                        $pend = $booking->start;
                    }
                    $pts = Point::findOne(['customer' => $booking->customer, 'start' => $pstart, 'end' => $pend]);
                    if (empty($pts)) {
                        $pts = new Point;
                        $pts->attributes = [
                            'customer' => $booking->customer,
                            'start' => $pstart,
                            'end' => $pend,
                            'points' => 1,
                            'created_at' => time(),
                            'updated_at' => time(),
                        ];
                        $pts->save();
                    } else {
                        $pts->points = $pts->points + 1;
                        $pts->updated_at = time();
                        $pts->save(false);
                    }
                    //check if it is promotion
                    $promo = Yii::$app->settings->get('ticket', 'promotion');
                    if ($pts->points > $promo) {
                        $booking->is_promo = 1;
                        $booking->discount = $booking->price;
                        $pts->points = 0;
                        $pts->save(false);
                    }
                }

                if ($booking->save(false)) {
                    $success['success'] = true;
                    $success['message'] = $this->formatTicket($booking);
                    //update ReservedSeats
                    $this->db->createCommand()->update(
                        'ReservedSeats',
                        [
                            'status' => Ticket::STATUS_CONFIRMED,
                        ],
                        ['ticket' => $booking->id]
                    )->execute();

                    $customerNumber = $booking->customer;
                    //1hr before boarding
                    //change hours to minutes
                    $timeExp = explode('H', $booking->dept_time);
                    $totalMin = ($timeExp[0] * 60) + $timeExp[1];
                    $totalMinDiff = $totalMin - 60;
                    //chenge back to 13H00 Format
                    $hr = intval($totalMinDiff / 60);
                    $min = $totalMinDiff % 60;
                    $time = sprintf("%02dH%02d", $hr, $min);

                    $message = "Hello! Ticket:{$booking->ticket}, {$booking->start}-{$booking->end} Seat:{$booking->seat}, {$booking->dept_date},{$booking->dept_time}. Inquiries call {$booking->routeR->customer_care}. Thanks for choosing VOLCANO EXPRESS";
                    //save SMS ready for sending
                    /*$this->db->createCommand()->insert('SMS', [
                        'ticket' => $booking->id,
                        'customer' => $customerNumber,
                        'route' => $booking->route,
                        'dept_date' => $booking->dept_date,
                        'dept_time' => $booking->dept_time,
                        'message' => $message,
                        'created_at' => time(),
                        'updated_at' => time(),
                        'created_by' => Yii::$app->user->id,
                        'updated_by' => Yii::$app->user->id,
                    ])->execute();*/
                } else {
                    $success['success'] = false;
                    $success['message'] = 'Ticketing failed. Try again!';
                }
            }
        }

        if ($success['success']) {
            $success['data'] = $success['message'];
            $success['message'] = '';
        }
        return $success;
    }

    public function actionTicketsv2($action = 'buy')
    {
        $result = Yii::$app->runAction('/v1/tickets/tickets', ['action' => $action]);
        if ($result['success']) {
            $result['data'] = $result['tickets'];
            $result['message'] = '';
        }
        return $result;
    }

    public function actionIntraCityTicketsOld()
    {
        $post = json_decode(file_get_contents('php://input'), true);
        if (is_null($post)) {
            $success['success'] = false;
            $success['message'] = 'Machine sent invalid Request';
            return $success;
        }

        if (empty($post['card'])) {
            $success['success'] = false;
            $success['message'] = 'Missing user card';
            return $success;
        }

        if (empty($post['start']) || empty($post['end'])) {
            $success['success'] = false;
            $success['message'] = 'Missing details';
            return $success;
        }

        $paymentApiBaseURL = 'https://eshyura.lapafrica.com/index.php/api/v1/';
        $lapWallet = '8279072421303279';
        $appId = '1682503182443014771';
        $auth = [$appId, '060050daae3c6b0d4120bbfef665d127d93935372264a736fbd9c7079e184243'];

        $client = new Client([
            'base_uri' => $paymentApiBaseURL,
            'timeout' => 60, //1min
        ]);

        //get user details
        $response = $client->request('GET', 'users/profile-by-card', [
            'query' => ['card' => $post['card']],
            'auth' => $auth,
        ]);

        //normal ticketing
        $settings = Yii::$app->settings;
        $success = [];

        $msg = $this->isAllowed($post['pos']);
        if (is_array($msg)) {
            return $msg;
        }

        $customerDetails = json_decode($response->getBody(), true);
        if (!$customerDetails['success']) {
            return $customerDetails;
        } //failed to get user details

        $customerName = $customerDetails['data']['fullname'];
        $customerMobile = $customerDetails['data']['mobile'];
        $customerWallet = $customerDetails['data']['wallet'];
        $customerLang = $customerDetails['data']['language'];

        $start = $post['start'];
        $end = $post['end'];
        $seat = $post['seat'];
        $time = $post['time'];
        $routeId = $post['route'];
        $serial = '0';
        $points = 0;
        $disc = 0;

        if ($settings->has('ticket', 'no-discount')) {
            if ($settings->get('ticket', 'no-discount') == 1) {
                $disc = '0';
            }
        }

        if (!empty($post['ticket_serial'])) {
            $serial = $post['ticket_serial'];

            $serialTicket = Ticket::find()
                ->where(['serial_number' => $serial])
                ->andWhere(['status' => Ticket::STATUS_CONFIRMED])
                ->one();

            //update status to deleted before re-making
            //Release The Tickets seats
            if (!empty($serialTicket)) {
                $serialTicket->status = Ticket::STATUS_CANCELLED;
                $this->db->createCommand()->delete('ReservedSeats', ['ticket' => $serialTicket->id])->execute();
                $serialTicket->update(false);
            }
        }

        $customerModel = Customer::findOne($customerMobile);
        if (empty($customerModel)) {
            $customerModel = new Customer;
            $customerModel->mobile = $customerMobile;
            $customerModel->name = $customerName;

            if (!$customerModel->save()) {
                if (!empty($customerModel->getFirstError('mobile'))) {
                    $success['message'] = 'Customer Mobile Incorrect/Missing';
                } elseif (!empty($customerModel->getFirstError('name'))) {
                    $success['message'] = 'Customer Name Missing';
                }

                Yii::error($customerModel->errors);
                $success['success'] = false;
                return $success;
            }
        }
        $originalDate = $post['date'];
        $origDateUnixTs = strtotime($originalDate . ' ' . str_replace('H', ':', $time));
        $date = date("Y-m-d", $origDateUnixTs);
        //get the requested route
        //first check if it has the start-end stops
        //if it does, great! Else check if it is return route
        //if none of the two matches, no such route here
        $customerRoute = Route::findOne($routeId);
        if (empty($customerRoute)) {
            $success['success'] = false;
            $success['message'] = 'Invalid Route';
            return $success;
        }

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
        //return $customerRoute;
        $currency = $post['currency'];

        //find price
        $price = $this->db->createCommand('SELECT price FROM Pricing WHERE TRIM(route)=:route AND ((TRIM(start)=:start AND TRIM(end)=:end)) AND TRIM(currency)=:currency')
            ->bindValue(':start', $start)
            ->bindValue(':end', $end)
            ->bindValue(':route', $customerRoute->id)
            ->bindValue(':currency', $currency)
            ->queryScalar();

        //in case there is no price, test and see if the return route have any
        if ($price == false) {
            $price = $this->db->createCommand('SELECT price FROM Pricing WHERE TRIM(route)=:route AND (TRIM(start)=:end AND TRIM(end)=:start) AND TRIM(currency)=:currency')
                ->bindValue(':start', $start)
                ->bindValue(':end', $end)
                ->bindValue(':route', $customerRoute->id)
                ->bindValue(':currency', $currency)
                ->queryScalar();
        }

        //check if time have passed
        if ($price == false) {
            $success['success'] = false;
            $success['message'] = 'No Price for  Route/Currency!';
            //$success['message'] = 'You cannot sell for this Bus';
            return $success;
        }

        //get planned routes for date and hour
        $bus = null;
        $busSeats = [];
        $message = "No Bus for " . Yii::$app->formatter->asDate($date, 'dd-M-yy') . " " . $time;
        $proute = null;

        $proutes = PlannedRoute::find()->where([
            'dept_date' => $date,
            'dept_time' => $time,
            'route' => $customerRoute->id,
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
            $success['message'] = $message;
            return $success;
        } elseif ($proute->is_active == 0) {
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
            //is it intl route? Enforce details
            if ($customerRoute->is_intl == 1 && $this->intlCustInfoMissing($customerModel)) {
                $success['success'] = false;
                $success['message'] = 'Customer Info Missing';
                return $success;
            }
            //check seat if is free
            $busSeats = $this->getFreeSeats($customerRoute->id, $start, $end, $date, $time, $proute->capacity, $bus->regno);
            //var_dump($busSeats); die();
            $seatCount = count($busSeats);
            if ($seatCount == 0) {
                $success['success'] = false;
                //$success['message'] = "Bus {$proute->bus} is full";
                $success['message'] = "Bus is full";
                return $success;
            }

            if (intval($seat) == 0) { //seat passed is 0 so select any seat for him/her
                //no specific seat find any free
                $seat = $busSeats[0]; //first free seats
            } elseif ($seat > $proute->capacity || $seat < 0) {
                $success['success'] = false;
                $success['message'] = "Seat $seat invalid!";
                return $success;
            } elseif (!in_array($seat, $busSeats)) {
                $success['success'] = false;
                $success['message'] = "Seat $seat occupied!";
                return $success;
            }
            //convert seat to int
            $seat = intval($seat);

            //reverse order ready for popping up
            $busSeats = array_reverse($busSeats);

            try {
                //generate ticket
                $ticketModel = new Ticket;
                $ticketModel->rura = 0; //it is visible on different portal avoid doubling
                $ticketModel->bus = $bus->regno;
                $ticketModel->seat = $seat;
                $ticketModel->route = $customerRoute->id;
                $ticketModel->start = $start;
                $ticketModel->end = $end;
                $ticketModel->dept_date = $date;
                $ticketModel->dept_time = $time;
                $ticketModel->customer = $customerMobile;
                $ticketModel->is_staff = 0;
                $ticketModel->issued_on = time();
                $ticketModel->machine_serial = $post['pos'];
                $ticketModel->currency = $post['currency'];
                $ticketModel->mobile_money = 'eshyura';
                $ticketModel->is_promo = 0;
                $ticketModel->is_deleted = 0;
                $ticketModel->serial_number = $serial;
                //for points
                $pstart = $start;
                $pend = $end;
                //update Ticket field and send results
                $factory = new \RandomLib\Factory;
                $generator = $factory->getGenerator(new \SecurityLib\Strength(\SecurityLib\Strength::MEDIUM));

                if ($ticketModel->is_staff == 1) {
                    $ticketModel->status = Ticket::STATUS_CONFIRMED;
                    $ticketModel->expired_in = 0;
                    $ticketModel->discount = $price;
                    $ticketModel->price = $price;
                } else {
                    $ticketModel->status = Ticket::STATUS_CONFIRMED;
                    $ticketModel->expired_in = 0;
                    $ticketModel->discount = $disc;
                    $ticketModel->price = $price;
                    $kstart = $ticketModel->start;
                    //generate the other remaining
                    if ($customerRoute->has_promotion == 1) {
                        $pstart = $ticketModel->start;
                        $pend = $ticketModel->end;

                        if ($customerRoute->is_return == 1) {
                            //to make life easier in handling points all return routes
                            //are converted to going route equivalent and then incerted that way
                            //GITEGA BUJA will be added as BUJA GITEGA
                            $pstart = $ticketModel->end;
                            $pend = $ticketModel->start;
                        }

                        $ticketModel->start = $kstart;
                        $pts = Point::findOne([
                            'customer' => $customerModel->mobile,
                            'start' => $pstart,
                            'end' => $pend
                        ]);
                        if (empty($pts)) {
                            $pts = new Point;
                            $pts->attributes = [
                                'customer' => $customerModel->mobile,
                                'start' => $pstart,
                                'end' => $pend,
                                'points' => 1,
                                'created_at' => time(),
                                'updated_at' => time(),
                            ];
                            $pts->save();
                        } else {
                            $pts->points = $pts->points + 1;
                            $pts->updated_at = time();
                            $pts->save(false);
                        }
                        //check if it is promotion
                        $promo = $settings->get('ticket', 'promotion');
                        if ($pts->points > $promo) {
                            $ticketModel->is_promo = 1;
                            $ticketModel->discount = $ticketModel->price;
                            $pts->points = 0;
                            $pts->save(false);
                        }
                        $points = $pts->points;
                    }
                }
                $ticketModel->ticket = $generator->generateString(12, 'ABCDEFGHIJKL09876MNOPQRSTUVWXYZ54321');

                //save changes
                if (!$ticketModel->save()) {
                    Yii::error(json_encode($ticketModel->errors) . '|' . json_encode($ticketModel->attributes));
                    $success['success'] = false;
                    $success['message'] = 'failed to save the ticket';
                    return $success;
                }

                //store the stuffs in the reserved table
                $btnRoutes = $this->getPathRoutes($ticketModel->start, $ticketModel->end, $ticketModel->route);

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

                $timeExp = explode('H', $ticketModel->dept_time);
                $totalMin = ($timeExp[0] * 60) + $timeExp[1];
                $totalMinDiff = $totalMin - 60;
                //chenge back to 13H00 Format
                $hr = intval($totalMinDiff / 60);
                $min = $totalMinDiff % 60;
                $time = sprintf("%02dH%02d", $hr, $min);
                //pay for ticket
                $response = $client->request('GET', 'users/pay-merchant', [
                    'auth' => $auth,
                    'json' => [
                        'from' => $customerWallet,
                        'card' => $post['card'],
                        'to' => $lapWallet,
                        'amount' => $ticketModel->price,
                        'currency' => $ticketModel->currency,
                        'merchant' => 'LAPLTD',
                        'reference' => $ticketModel->ticket,
                        'provider' => 'ABR661',
                        'device' => $post['pos'],
                        'app_id' => $appId,
                        'description' => "{$ticketModel->start}-{$ticketModel->end}",
                    ]
                ]);

                $json = json_decode($response->getBody(), true);
                if (!$json['success']) {
                    $success['success'] = false;
                    $success['message'] = $json['message'];
                    return $success;
                } else {
                    $balance = "{$json['wallet']['balance']} {$ticketModel->currency}";

                    $messageFr = "Bonjour, Votre ticket:{$ticketModel->ticket}, Allant de {$ticketModel->start}-{$ticketModel->end}  {$ticketModel->dept_date}.Balance {$balance}. Appelez {$customerRoute->customer_care}. Merci!";
                    $messageKisw = "Habari, tiketi yako ni {$ticketModel->ticket}, {$ticketModel->start}-{$ticketModel->end} itaondoka {$ticketModel->dept_date}. Salio {$balance}. Maulizo {$customerRoute->customer_care}. Ahsante!";
                    $messageRw = "Muraho, tike:{$ticketModel->ticket}, {$ticketModel->start}-{$ticketModel->end} igenda {$ticketModel->dept_date}. Mufite {$balance}. Ubufasha {$customerRoute->customer_care}. Murakoze!";
                    $messageEn = "Hi, your ticket:{$ticketModel->ticket}, {$ticketModel->start}-{$ticketModel->end} departing on {$ticketModel->dept_date}.Balance {$balance}. Inquiries  {$customerRoute->customer_care}. Thanks!";

                    $message = '';
                    if ($customerLang == 'fr') {
                        $message = $messageFr;
                    } elseif ($customerLang == 'swa') {
                        $message = $messageKisw;
                    } elseif ($customerLang == 'kin') {
                        $message = $messageRw;
                    } else {
                        //default english
                        $message = $messageEn;
                    }

                    //save SMS ready for sending
                    /*$this->db->createCommand()->insert('SMS', [
                        'ticket' => $ticketModel->id,
                        'customer' => $customerMobile,
                        'route' => $ticketModel->route,
                        'dept_date' => $ticketModel->dept_date,
                        'dept_time' => $ticketModel->dept_time,
                        'message' => $message,
                        'created_at' => time(),
                        'updated_at' => time(),
                        'created_by' => Yii::$app->user->id,
                        'updated_by' => Yii::$app->user->id,
                    ])->execute();*/

                    $ticketId =  rtrim(chunk_split($ticketModel->ticket, 3, '-'), '-');
                    $success['success'] = true;
                    $success['message'] = Yii::t('app', 'Ticket generated: {t}, points {p}', ['t' => $ticketId, 'p' => $points]);
                    return $success;
                }
            } catch (\yii\db\IntegrityException $e) {
                $success['success'] = false;
                $success['message'] = 'Ticket Not generated. Try again!';
                Yii::error($e->getMessage(), 'ticket-intega-error');
                $ticketModel->delete();

                //reduce points
            } catch (\yii\base\Exception $e) {
                $success['success'] = false;
                $success['message'] = $e->getMessage(); //'Ticket generation failed. Try again';
                Yii::error($e->getMessage(), 'ticket-error');
                $ticketModel->delete();

                //reduce points
            }
        }

        return $success;
    }

    public function actionMobileTickets()
    {
        $result = Yii::$app->runAction('/v1/tickets/tickets', ['action' => 'booking', 'isMobile' => true]);
        if ($result['success']) {
            unset($result['message']['common']['discount']);
            unset($result['message']['common']['pos']);
        }
        return $result;
    }

    public function formatSerialTicket($ticket)
    {
        $success = [];
        $pstart = $ticket->start;
        $pend = $ticket->end;

        if ($ticket->start == 'REMERA') {
            $ticket->start = 'NYABUGOGO';
        }
        if ($ticket->start == 'RONDPOINT') {
            $ticket->start = 'NGOMA';
        }
        if ($pstart == 'GITEGA') {
            if ($pend == 'BUJUMBURA') {
                $pstart = 'BUJUMBURA';
                $pend = 'GITEGA';
            }
        }


        $customerModel = Customer::findOne($ticket->customer);
        $success['message']['common'] = $this->formatTicket($ticket, $customerModel, true);
        $success['success'] = true;

        //points
        $points =  $this->db->createCommand('SELECT SUM(points) FROM Points WHERE customer=:customer AND start=:start AND end=:end')
            ->bindValue(':customer', $ticket->customer)
            ->bindValue(':start', $pstart)
            ->bindValue(':end', $pend)
            ->queryScalar();

        $tinfo = [];
        $tinfo['id'] = rtrim(chunk_split($ticket->ticket, 3, '-'), '-');
        $tinfo['points'] = empty($points) ? '0' : $points;
        $tinfo['is_promo'] = $ticket->is_promo;
        $tinfo['seat'] = $ticket->seat;

        $success['message']['tickets'][] = $tinfo;

        return $success;
    }

    public function actionTickets($action = 'buy', $count = 1, $isMobile = false)
    {
        $settings = Yii::$app->settings;
        $isOldPos = false;
        $success = [];
        //return ['success'=>false, 'message'=>'Temporarily Suspendeded'];
        //limit to 10
        if ($count > 10) {
            $count = 10;
        }

        $data = Yii::$app->request->post();
        if (count($data) == 1 && is_array($data)) {
            $data = json_decode(array_key_first($data), true);
            $isOldPos = true;
        }

        if (!isset($data['ticket_serial']) && $isOldPos) {
            $data['ticket_serial'] = strval(time());
        }

        if (empty($data)) {
            $success['success'] = false;
            $success['message'] = 'Machine sent invalid Request';
            return $success;
        }

        //is it an old ticket request via old POS?
        unset($data['card'], $data['plateNo'], $data['capacity']);

        //overwrite data (and put if does not exist)
        $data['discount'] = '0';

        $msg = $this->isAllowed($data['pos']);
        if (is_array($msg)) {
            return $msg;
        }

        $customer = $data['customer'];
        unset($data['customer']);

        $disc = $data['discount'];

        if ($settings->has('ticket', 'no-discount')) {
            if ($settings->get('ticket', 'no-discount') == 1) {
                $disc = '0';
            }
        }

        //if number is zero do some doctoring
        if (intval($customer['number']) == 0) {
            //fake the number
            $customer['number'] = round(microtime(true) * 1000) . ''; // must be a string
        }

        //staff cannot book
        if ($this->isStaff($customer['number']) && $action == 'booking') {
            $success['success'] = false;
            $success['message'] = "Staff Cannot do Booking! $action";
            return $success;
        }

        //limit booking

        $bookcount = 0;
        if ($action == 'booking') {
            //if(110001)
            if (Yii::$app->user->id == 110001) {
                $isMobile = true;
            }
            $bookcount = 1;
        }

        //check route
        $start = $data['start'];
        $end = $data['end'];
        $seat = $data['seat'];
        $originalDate = $data['date'];
        $time = $data['time'];
        $routeId = $data['route'];
        $pos = $data['pos'];
        $serial = $data['ticket_serial'];

        $bagTags = isset($data['bag_tags']) ? $data['bag_tags'] : [];

        unset($data['time']);
        unset($data['date']);
        unset($data['pos']);
        unset($data['bag_tags']);
        unset($data['ticket_serial']);

        unset($data['customer']['age']);
        unset($data['customer']['passport']);

        if (!empty($data['ticket_serial'])) {
            $serial = $data['ticket_serial'];

            $serialTicket = Ticket::find()
                ->where(['serial_number' => $serial])
                ->andWhere(['status' => Ticket::STATUS_CONFIRMED])
                ->one();

            //update status to deleted before re-making
            //Release The Tickets seats
            if (!empty($serialTicket)) {
                Yii::error("SERIAL: $serialTicket");
                $serialTicket->status = Ticket::STATUS_CANCELLED;
                $this->db->createCommand()->delete('ReservedSeats', ['ticket' => $serialTicket->id])->execute();
                $serialTicket->update(false);
            }

            //comment old one that does reprint
            //return $this->formatSerialTicket($serialTicket);
        }

        //you cannot select seat and issue more than one ticket
        if (intval($data['seat']) != 0 && $count > 1) {
            $success['success'] = false;
            $success['message'] = "Leave Seat 0 for multiple tickets";
            return $success;
        }

        $db = Ticket::getDb();
        $transaction = $db->beginTransaction();
        try {
            $customerModel = Customer::findOne($customer['number']);
            if (empty($customerModel)) {
                $customerModel = new Customer;
                $customerModel->mobile = $customer['number'];
                $customerModel->name = $customer['name'];
                $customerModel->nationality = $customer['nationality'];
                $customerModel->gender = $customer['gender']; // 1- male 2 - female
                $customerModel->dob = isset($customer['dob']) ? $customer['dob'] : null;
                $customerModel->created_at = time();
                $customerModel->updated_at = time();
                $customerModel->created_by = Yii::$app->user->id;
                $customerModel->updated_by = Yii::$app->user->id;

                if (!$customerModel->save()) {
                    Yii::error($customerModel->errors);
                    if (!empty($customerModel->getFirstError('mobile'))) {
                        $success['message'] = 'Customer Mobile Incorrect/Missing';
                    } elseif (!empty($customerModel->getFirstError('name'))) {
                        $success['message'] = 'Customer Name Missing';
                    } else {
                        $success['message'] = 'Invalid or missing customer information';
                    }

                    Yii::error($customerModel->errors);
                    $success['success'] = false;
                    throw new ServerErrorHttpException($success['message']);
                }
            }

            $origDateUnixTs = strtotime($originalDate . ' ' . str_replace('H', ':', $time));
            $date = date("Y-m-d", $origDateUnixTs);

            //check if time have passed
            $checkForBookingTime = false;

            if ($bookcount == 1) {
                $bcount =  $this->db->createCommand('SELECT COUNT(id) FROM Tickets 
                                WHERE start=:start AND end = :end AND route=:route  AND dept_date = :date AND dept_time = :time
                                AND status = :status')
                    ->bindValue(':start', $start)
                    ->bindValue(':end', $end)
                    ->bindValue(':route', $routeId)
                    ->bindValue(':date', $date)
                    ->bindValue(':time', $time)
                    ->bindValue(':status', 'BO')
                    ->queryScalar();
                if ($bcount >= 50) {
                    $success['success'] = false;
                    $success['message'] = "Booking limit has reached(10/bus)! $action";
                    throw new ServerErrorHttpException($success['message']);
                }
            }



            if ($settings->has('ticket', 'check-booking-time')) {
                $checkForBookingTime = $settings->get('ticket', 'check-booking-time') == 1;
            }

            if ($origDateUnixTs < time() && $checkForBookingTime) {
                $success['success'] = false;
                $success['message'] = 'Booking Time Passed!';
                throw new ServerErrorHttpException($success['message']);
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
            if (empty($customerRoute)) {
                $success['success'] = false;
                $success['message'] = 'Invalid Route';
                return $success;
            }

            if (!$customerRoute->hasRoute($start, $end)) {
                //check if it is return reoute
                if ($customerRoute->returnR && $customerRoute->returnR->hasRoute($start, $end)) {
                    //the stops suggests this is a return route not a go route
                    $customerRoute = $customerRoute->returnR;
                } else {
                    $success['success'] = false;
                    $success['message'] = 'No Route/Stops for the Bus!';
                    throw new ServerErrorHttpException($success['message']);
                }
            }
            //return $customerRoute;

            $currency = $data['currency'];

            //find price
            $price = $this->db->createCommand('SELECT price FROM Pricing WHERE TRIM(route)=:route AND ((TRIM(start)=:start AND TRIM(end)=:end)) AND TRIM(currency)=:currency')
                ->bindValue(':start', $start)
                ->bindValue(':end', $end)
                ->bindValue(':route', $customerRoute->id)
                ->bindValue(':currency', $currency)
                ->queryScalar();

            //in case there is no price, test and see if the return route have any
            if ($price == false) {
                $price = $this->db->createCommand('SELECT price FROM Pricing WHERE TRIM(route)=:route AND (TRIM(start)=:end AND TRIM(end)=:start) AND TRIM(currency)=:currency')
                    ->bindValue(':start', $start)
                    ->bindValue(':end', $end)
                    ->bindValue(':route', $customerRoute->id)
                    ->bindValue(':currency', $currency)
                    ->queryScalar();
            }

            //check if time have passed
            if ($price == false) {
                $success['success'] = false;
                $success['message'] = 'No Price for  Route/Currency!';
                //$success['message'] = 'You cannot sell for this Bus';
                throw new ServerErrorHttpException($success['message']);
            }

            //get planned routes for date and hour
            $bus = null;
            $busSeats = [];

            $message = "No Bus for " . Yii::$app->formatter->asDate($date, 'dd-M-yy') . " " . $time;


            $proute = null;

            $proutes = PlannedRoute::find()->where([
                'dept_date' => $date,
                'dept_time' => $time,
                'route' => $customerRoute->id,
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
                $success['message'] = $message;
                throw new ServerErrorHttpException($success['message']);
            } elseif ($proute->is_active == 0) {
                $success['success'] = false;
                $success['message'] = 'Bus is Locked cannot sell';
                throw new ServerErrorHttpException($success['message']);
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
                //is it intl route? Enforce details
                if ($customerRoute->is_intl == 1) { //TODO: Check if some details are missing aka intlCustInfoMissing
                    //$success['success'] = false;
                    // $success['message'] = 'Customer Info Missing';
                    //return $success;
                }
                //check seat if is free
                $busSeats = $this->getFreeSeats($customerRoute->id, $start, $end, $date, $time, $proute->capacity, $bus->regno);
                //var_dump($busSeats); die();
                $seatCount = count($busSeats);
                if ($seatCount == 0) {
                    $success['success'] = false;
                    //$success['message'] = "Bus {$proute->bus} is full";
                    $success['message'] = "Bus is full";
                    throw new ServerErrorHttpException($success['message']);
                } elseif ($seatCount < $count) {
                    $success['success'] = false;
                    $success['message'] = "{$seatCount} Seats for {$proute->bus} available";
                    throw new ServerErrorHttpException($success['message']);
                }

                if (intval($seat) == 0) { //seat passed is 0 so select any seat for him/her
                    //no specific seat find any free
                    $seat = $busSeats[0]; //first free seats
                } elseif ($seat > $proute->capacity || $seat < 0) {
                    $success['success'] = false;
                    $success['message'] = "Seat $seat invalid!";
                    throw new ServerErrorHttpException($success['message']);
                } elseif (!in_array($seat, $busSeats)) {
                    $success['success'] = false;
                    $success['message'] = "Seat $seat occupied!";
                    throw new ServerErrorHttpException($success['message']);
                }
                //convert seat to int
                $seat = intval($seat);
                //ticket

                //reverse order ready for popping up
                $busSeats = array_reverse($busSeats);
                $isPrintStaffTicket = Yii::$app->settings->get('ticket', 'print-staff-ticket');

                for ($i = 0; $i < $count; $i++) {
                    //generate ticket
                    $ticketModel = new Ticket($data);

                    $ticketModel->bus = $bus->regno;
                    $ticketModel->seat = $count > 1 ? array_pop($busSeats)  : $seat;
                    $ticketModel->route = $customerRoute->id;
                    $ticketModel->start = $start;
                    $ticketModel->end = $end;
                    $ticketModel->dept_date = $date;
                    $ticketModel->dept_time = $time;
                    $ticketModel->customer = $customer['number'];
                    $ticketModel->is_staff = $isPrintStaffTicket == 0 ? 0 : $this->isStaff($customer['number']);
                    $ticketModel->issued_on = time();
                    $ticketModel->machine_serial = $pos;
                    $ticketModel->is_promo = 0;
                    $ticketModel->is_deleted = 0;
                    $ticketModel->serial_number = $serial;

                    //set all tickets printed except mobile tickets

                    if (!$isMobile) {
                        $ticketModel->is_printed = 1;
                    }


                    //for points
                    $pstart = $start;
                    $pend = $end;

                    //update Ticket field and send results
                    $factory = new \RandomLib\Factory;
                    $generator = $factory->getGenerator(new \SecurityLib\Strength(\SecurityLib\Strength::MEDIUM));

                    if ($action == 'buy') {
                        if ($ticketModel->is_staff == 1) {
                            $ticketModel->status = Ticket::STATUS_CONFIRMED;
                            $ticketModel->expired_in = 0;
                            $ticketModel->discount = $price;
                            $ticketModel->price = $price;
                        } else {
                            $ticketModel->status = Ticket::STATUS_CONFIRMED;
                            $ticketModel->expired_in = 0;
                            $ticketModel->discount = $disc;
                            $ticketModel->price = $price;
                            $kstart = $ticketModel->start;
                            //generate the other remaining
                            if ($customerRoute->has_promotion == 1 && strlen($customerModel->mobile) <= 12) { //prevent autogen mobiles from having the points
                                if ($ticketModel->start == 'REMERA') {
                                    $ticketModel->start = 'NYABUGOGO';
                                }
                                if ($ticketModel->start == 'RONDPOINT') {
                                    $ticketModel->start = 'NGOMA';
                                }
                                $pstart = $ticketModel->start;
                                $pend = $ticketModel->end;

                                if ($customerRoute->is_return == 1) {
                                    //to make life easier in handling points all return routes
                                    //are converted to going route equivalent and then incerted that way
                                    //GITEGA BUJA will be added as BUJA GITEGA
                                    $pstart = $ticketModel->end;
                                    $pend = $ticketModel->start;
                                }

                                $ticketModel->start = $kstart;
                                if ($pstart == 'GITEGA') {
                                    if ($pend == 'BUJUMBURA') {
                                        $pstart = 'BUJUMBURA';
                                        $pend = 'GITEGA';
                                    }
                                }

                                $pts = Point::findOne([
                                    'customer' => $customerModel->mobile,
                                    'start' => $pstart,
                                    'end' => $pend
                                ]);
                                if (empty($pts)) {
                                    $pts = new Point;
                                    $pts->attributes = [
                                        'customer' => $customerModel->mobile,
                                        'start' => $pstart,
                                        'end' => $pend,
                                        'points' => 1,
                                        'created_at' => time(),
                                        'updated_at' => time(),
                                    ];
                                    $pts->save();
                                } else {
                                    $pts->points = $pts->points + 1;
                                    $pts->updated_at = time();
                                    $pts->save(false);
                                }
                                //check if it is promotion
                                $promo = $settings->get('ticket', 'promotion');;
                                if ($pts->points > $promo) {
                                    $ticketModel->is_promo = 1;
                                    $ticketModel->discount = $ticketModel->price;
                                    $pts->points = 0;
                                    $pts->save(false);
                                }
                            }
                        }
                        $ticketModel->ticket = $generator->generateString(12, 'ABCDEFGHIJKL09876MNOPQRSTUVWXYZ54321');
                        $ticketModel->bag_tags = implode(',', $bagTags);
                        $ticketModel->number_of_bags = count($bagTags) > 0 ? count($bagTags) : null;
                    } else { //booking
                        $expiry = $settings->get('ticket', 'expiry');

                        if ($isMobile) {
                            $expiry = 60; //1minute
                            if ($settings->has('ticket', 'mobile-booking-expiry')) {
                                $expiry = $settings->get('ticket', 'mobile-booking-expiry');
                            }

                            $ticketModel->status = Ticket::STATUS_TEMPORARY_BOOKED;
                            $ticketModel->expired_in = $expiry;
                        } else {
                            $ticketModel->status = Ticket::STATUS_BOOKED;
                            $ticketModel->expired_in = $expiry;
                        }

                        $ticketModel->discount = $disc;
                        $ticketModel->price = $price;
                        $ticketModel->ticket = $generator->generateString(6, 'Z0C9A8P7E6B51N2C34M5D');
                    }
                    //save changes
                    if (!$ticketModel->save()) {
                        Yii::error(json_encode($ticketModel->errors) . '|' . json_encode($ticketModel->attributes));
                        continue; //skip erronous model
                    }

                    //store the stuffs in the reserved table
                    $btnRoutes = $this->getPathRoutes($ticketModel->start, $ticketModel->end, $ticketModel->route);

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


                    if ($i == 0) { //the first ticket, format the values
                        if ($isOldPos) {
                            $success['message']['common'] = $this->formatTicketOld($ticketModel, $customerModel, true);
                        } else {
                            $success['tickets']['common'] = $this->formatTicket($ticketModel, $customerModel, true);
                        }
                    }

                    $success['success'] = true;

                    //points
                    $points =  $this->db->createCommand('SELECT SUM(points) FROM Points WHERE customer=:customer AND start=:start AND end=:end')
                        ->bindValue(':customer', $ticketModel->customer)
                        ->bindValue(':start', $pstart)
                        ->bindValue(':end', $pend)
                        ->queryScalar();

                    $tinfo = [];
                    $tinfo['id'] = rtrim(chunk_split($ticketModel->ticket, 3, '-'), '-');
                    $tinfo['points'] = empty($points) ? '0' : $points;
                    $tinfo['is_promo'] = $ticketModel->is_promo;
                    $tinfo['seat'] = $ticketModel->seat;

                    if ($isOldPos) {
                        $success['message']['tickets'][] = $tinfo;
                    } else {
                        $success['tickets']['tickets'][] = $tinfo;
                    }

                    //print only one booking
                    if ($ticketModel->status == Ticket::STATUS_BOOKED) {
                        break;
                    }


                    if ($ticketModel->status != Ticket::STATUS_BOOKED && $customerRoute->send_sms == 1) {
                        $customerNumber = $ticketModel->customer;
                        /*if ($ticketModel->start=='KAMPALA') {
                            if (substr($customerNumber, 0, 1)=='0') {//number starts with 0
                                $customerNumber = substr($customerNumber, 1);
                            }
                            //if number does not start with 256
                            if (substr($customerNumber, 0, 3)!='256' && substr($customerNumber, 0, 4)!='+256') {
                                $customerNumber = '256'.$customerNumber;
                            }
                        }*/
                        //1hr before boarding
                        //change hours to minutes
                        $timeExp = explode('H', $ticketModel->dept_time);
                        $totalMin = ($timeExp[0] * 60) + $timeExp[1];
                        $totalMinDiff = $totalMin - 60;
                        //chenge back to 13H00 Format
                        $hr = intval($totalMinDiff / 60);
                        $min = $totalMinDiff % 60;
                        $time = sprintf("%02dH%02d", $hr, $min);

                        //save SMS ready for sending
                        /*$this->db->createCommand()->insert('SMS', [
                            'ticket' => $ticketModel->id,
                            'customer' => $customerNumber,
                            'route' => $ticketModel->route,
                            'dept_date' => $ticketModel->dept_date,
                            'dept_time' => $ticketModel->dept_time,
                            'message' => "Hello! Ticket:{$ticketModel->ticket}, {$ticketModel->start}-{$ticketModel->end} Seat:{$ticketModel->seat}, {$ticketModel->dept_date},{$ticketModel->dept_time}. Inquiries call {$customerRoute->customer_care}. Thanks for choosing VOLCANO EXPRESS",
                            'created_at' => time(),
                            'updated_at' => time(),
                            'created_by' => Yii::$app->user->id,
                            'updated_by' => Yii::$app->user->id,
                        ])->execute();*/
                    }
                }
            }
            $transaction->commit();
            return $success;
        } catch (ServerErrorHttpException $e) {
            $success['success'] = false;
            $success['message'] = $e->getMessage();
            $transaction->rollBack();
        } catch (Exception $e) {
            $success['success'] = false;
            $success['message'] = 'Ticket Not generated. Try again!';
            Yii::error($e, 'ticket-intega-error');
            $transaction->rollBack();
        }
        return $success;
    }

    public function actionIntraCityTickets()
    {
        $settings = Yii::$app->settings;
        $success = [];

        $data = Yii::$app->request->post();

        if (empty($data)) {
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
        $date = $data['dept_date'];
        $time = $data['dept_time'];
        $routeId = $data['route'];
        $pos = $data['pos'];


        $db = Ticket::getDb();
        $transaction = $db->beginTransaction();
        try {
            $user = Yii::$app->user->identity;
            $customerModel = Customer::findOne($user->id);
            if (empty($customerModel)) {
                $customerModel = new Customer([
                    'mobile' => strval($user->id),
                    'name' => $user->name,
                ]);
                if (!$customerModel->save()) {
                    throw new ServerErrorHttpException('Cannot save customer, try again!');
                }
            }

            //get the requested route
            //first check if it has the start-end stops
            //if it does, great! Else check if it is return route
            //if none of the two matches, no such route here
            $customerRoute = Route::findOne($routeId);
            if (empty($customerRoute)) {
                throw new ServerErrorHttpException('Invalid Route');
            }

            if (!$customerRoute->hasRoute($start, $end)) {
                //check if it is return reoute
                if ($customerRoute->returnR && $customerRoute->returnR->hasRoute($start, $end)) {
                    //the stops suggests this is a return route not a go route
                    $customerRoute = $customerRoute->returnR;
                } else {
                    throw new ServerErrorHttpException('No Route/Stops for the Bus!');
                }
            }
            //return $customerRoute;

            $currency = $data['currency'];

            //find price
            $price = $this->db->createCommand('SELECT price FROM Pricing WHERE TRIM(route)=:route AND ((TRIM(start)=:start AND TRIM(end)=:end)) AND TRIM(currency)=:currency')
                ->bindValue(':start', $start)
                ->bindValue(':end', $end)
                ->bindValue(':route', $customerRoute->id)
                ->bindValue(':currency', $currency)
                ->queryScalar();

            //in case there is no price, test and see if the return route have any
            if ($price == false) {
                $price = $this->db->createCommand('SELECT price FROM Pricing WHERE TRIM(route)=:route AND (TRIM(start)=:end AND TRIM(end)=:start) AND TRIM(currency)=:currency')
                    ->bindValue(':start', $start)
                    ->bindValue(':end', $end)
                    ->bindValue(':route', $customerRoute->id)
                    ->bindValue(':currency', $currency)
                    ->queryScalar();
            }

            //check if time have passed
            if ($price == false) {
                $success['success'] = false;
                $success['message'] = 'No Price for  Route/Currency!';
                //$success['message'] = 'You cannot sell for this Bus';
                throw new ServerErrorHttpException($success['message']);
            }

            //get planned routes for date and hour
            $bus = null;
            $busSeats = [];

            $message = "No Bus for " . Yii::$app->formatter->asDate($date, 'dd-M-yy') . ' ' . $time;


            $proute = null;

            $proutes = PlannedRoute::find()->where([
                'dept_date' => $date,
                'dept_time' => $time,
                'route' => $customerRoute->id,
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
                $success['message'] = $message;
                throw new ServerErrorHttpException($success['message']);
            } elseif ($proute->is_active == 0) {
                $success['success'] = false;
                $success['message'] = 'Bus is Locked cannot sell';
                throw new ServerErrorHttpException($success['message']);
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
                throw new ServerErrorHttpException($message);
            }

            //check seat if is free
            $busSeats = $this->getFreeSeats($customerRoute->id, $start, $end, $date, $time, $proute->capacity, $bus->regno);
            //var_dump($busSeats); die();
            $seatCount = count($busSeats);
            if ($seatCount == 0) {
                $success['success'] = false;
                //$success['message'] = "Bus {$proute->bus} is full";
                $success['message'] = "Bus is full";
                throw new ServerErrorHttpException($success['message']);
            }

            $seat = $busSeats[0]; //first free seats
            //convert seat to int
            $seat = intval($seat);
            //ticket
            //generate ticket
            $factory = new \RandomLib\Factory;
            $generator = $factory->getGenerator(new \SecurityLib\Strength(\SecurityLib\Strength::MEDIUM));

            $ticketModel = new Ticket([
                'dept_date' => $date,
                'dept_time' => $time,
                'route' => $routeId,
                'start' => $start,
                'end' => $end,
                'bus' => $bus->regno,
                'seat' => $seat,
                'currency' => $currency,
                'customer' => $customerModel['mobile'],
                'is_staff' => 0,
                'is_promo' => 0,
                'is_deleted' => 0,
                'issued_on' => time(),
                'machine_serial' => $pos,
                'status' => Ticket::STATUS_CONFIRMED,
                'expired_in' => 0,
                'discount' => 0,
                'price' => $price,
                'ticket' => $generator->generateString(12, 'ABCDEFGHIJKL09876MNOPQRSTUVWXYZ54321'),
            ]);
            //save changes
            if (!$ticketModel->save()) {
                Yii::error($ticketModel->errors);
                throw new ServerErrorHttpException(Yii::t('app', 'Could not created Ticket. Please try again!'));
            }

            //store the stuffs in the reserved table
            $btnRoutes = $this->getPathRoutes($ticketModel->start, $ticketModel->end, $ticketModel->route);

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

            $success['success'] = true;
            $success['message'] = 'Ticket was created successfully!';
            $success['ticket'] = $this->formatTicket($ticketModel, $customerModel, true);

            $success['ticket']['id'] = rtrim(chunk_split($ticketModel->ticket, 3, '-'), '-');
            $success['ticket']['seat'] = $ticketModel->seat;

            $transaction->commit();
            Yii::error(json_encode($success));
            return $success;
        } catch (ServerErrorHttpException $e) {
            $success['success'] = false;
            $success['message'] = $e->getMessage();
            $transaction->rollBack();
        } catch (Exception $e) {
            $success['success'] = false;
            $success['message'] = 'Ticket Not generated. Try again!';
            Yii::error($e);
            $transaction->rollBack();
        }
        return $success;
    }

    public function actionSellCards()
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

        $status = 0;
        $start = 'KIGALI';
        $end = 'GICUMBI';

        $cards = RouteCard::find()->where([
            'is_sold' => $status,
            'start' => $start,
            'end' => $end,


        ])->orderBy('card ASC')->one();

        if (!preg_match("/^[a-zA-Z ]*$/", $data['name'])) {
            $success['success'] = false;
            $success['message'] = 'Invalid name!!!';
            return $success;
        }


        if (empty($cards)) {
            $card = RouteCard::findOne($data['card']);
        } else {
            $card = RouteCard::findOne($cards->card);
            if (strlen($data['card']) < 10 || strlen($data['card']) > 10) {
                $success['success'] = false;
                $success['message'] = 'Invalid phone number';
                return $success;
            }
        }

        // $card = RouteCard::findOne($cards->card);
        //$card = RouteCard::findOne($data['card']);

        if (empty($card)) {
            $success['success'] = false;
            $success['message'] = 'Card was not found';
            return $success;
        } elseif ($card->is_sold == 1) {
            $success['success'] = false;
            $success['message'] = "Card  {$card->card}  is  already sold";
            return $success;
        }
        //update information
        else {
            $card->is_sold = 1;
            $card->phone = $data['card'];
            $card->sold_by = Yii::$app->user->id;
            $card->updated_by = Yii::$app->user->id;
            $card->owner = $data['name'];
            $card->updated_at = time();
            $card->sold_at = time();
            $card->pos = $data['pos'];

            $customerModel = Customer::findOne($card->card);
            if (empty($customerModel)) {
                $customerModel = new Customer;
                $customerModel->mobile = $card->card . '';
                $customerModel->name = $card->owner;
                $customerModel->nationality = '';
                $customerModel->gender = -1; // 1- male 2 - female
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
                $success['message'] = "Succesfully sold {$data['card']}\nDetails:\nROUTE:{$card->start}-{$card->end}\nTRIPS:{$card->total_trips}";
                return $success;
            } else {
                $success['success'] = false;
                $success['message'] = "Could not sell {$card->card}. Try again!";
                return $success;
            }
        }
    }

    public function actionSellCardTickets($count = 1)
    {
        $settings = Yii::$app->settings;

        $success = [];

        //limit to 10
        if ($count > 10) {
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

        $no = $data['card'];
        $cards = RouteCard::find()->where([
            'phone' => $no,
            'is_sold' => 1,

        ])->orderBy('remaining_trips DESC')->one();

        if (empty($cards)) {
            $card = RouteCard::findOne($data['card']);
        } else {
            $card = RouteCard::findOne($cards->card);
        }

        if (empty($card)) {
            $success['success'] = false;
            $success['message'] = 'Card was not found';
            return $success;
        } elseif ($card->is_sold == 0) {
            $success['success'] = false;
            $success['message'] = "Card {$card->card} not sold!";
            return $success;
        }

        //there is trip remaining??
        if ($card->remaining_trips <= 0) {
            $success['success'] = false;
            $success['message'] = "Trips are finished for:{$card->card}";
            return $success;
        } elseif ($card->remaining_trips < $count) {
            $success['success'] = false;
            $success['message'] = "Remaining Trips for :{$card->card} are {$card->remaining_trips}";
            return $success;
        }

        $disc =  $card->price;

        $customerModel = Customer::findOne($card->card);

        //check route
        $start = $data['start'];
        $end = $data['end'];
        if (($card->start != $start && $card->start != $end) || $card->end != $start && $card->end != $end) {
            $success['success'] = false;
            $success['message'] = "You can only sell {$card->start}-{$card->end}";
            return $success;
        }
        $seat = 0;
        $time = $data['time'];
        $routeId = $data['route'];
        $priceRoute = null;

        $originalDate = $data['date'];
        $origDateUnixTs = strtotime($originalDate . ' ' . str_replace('H', ':', $time));
        $date = date("Y-m-d", $origDateUnixTs);

        //check if time have passed
        $checkForBookingTime = false;

        if ($settings->has('ticket', 'check-booking-time')) {
            $checkForBookingTime = $settings->get('ticket', 'check-booking-time') == 1;
        }

        if ($origDateUnixTs < time() && $checkForBookingTime) {
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

        if (empty($price) && intval($price) != 0) {
            $success['success'] = false;
            $success['message'] = 'No Price for  Route/Currency!';
            return $success;
        }

        //get planned routes for date and hour
        $bus = null;
        $busSeats = [];

        $message = "No Bus for " . Yii::$app->formatter->asDate($date, 'dd-M-yy') . " " . $time;

        $proute = null;

        $proute = null;

        $proutes = PlannedRoute::find()->where([
            'dept_date' => $date,
            'dept_time' => $time,
            'route' => $customerRoute->id,
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
        } elseif ($proute->is_active == 0) {
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
            if ($seatCount == 0) {
                $success['success'] = false;
                //$success['message'] = "Bus {$proute->bus} is full";
                $success['message'] = "Bus is full";
                return $success;
            } elseif ($seatCount < $count) {
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
                    for ($i = 0; $i < $count; $i++) {
                        $card->remaining_trips = $card->remaining_trips - 1;

                        if ($card->save(false)) {
                            //Log the card
                            $cl = new \app\models\CardLog;
                            $cl->attributes = [
                                'id' => $ticketModel->id,
                                'card' => $card->card,
                                'created_at' => time(),
                                'created_by' => Yii::$app->user->id,
                                'updated_at' => time(),
                                'updated_by' => Yii::$app->user->id,
                                'remained_trips' => $card->remaining_trips
                            ];
                            $cl->save(false);
                        }
                        //increment points
                        $pstart = $ticketModel->start;
                        $pend = $ticketModel->end;

                        if ($ticketModel->status == Ticket::STATUS_CARD_TICKET) {
                            if ($i > 0) { //the first model, ignore if
                                $ticketModel->isNewRecord = true;
                                $ticketModel->id = null;
                                $ticketModel->ticket = null;
                                $ticketModel->seat = $busSeats[$i];
                            }

                            $ticketModel->is_promo = 0;

                            //generate the other remaining
                            if ($customerRoute->has_promotion == 1) {
                                if ($customerRoute->is_return == 1) {
                                    //to make life easier in handling points all return routes
                                    //are converted to going route equivalent and then inserted that way
                                    //GITEGA BUJA will be added as BUJA GITEGA
                                    $pstart = $ticketModel->end;
                                    $pend = $ticketModel->start;
                                }

                                $pts = Point::findOne([
                                    'customer' => $customerModel->mobile,
                                    'start' => $pstart,
                                    'end' => $pend
                                ]);
                                if (empty($pts)) {
                                    $pts = new Point;
                                    $pts->attributes = [
                                        'customer' => $customerModel->mobile,
                                        'start' => $pstart,
                                        'end' => $pend,
                                        'points' => 1,
                                        'created_at' => time(),
                                        'updated_at' => time(),
                                    ];
                                    $pts->save();
                                } else {
                                    $pts->points = $pts->points + 1;
                                    $pts->updated_at = time();
                                    $pts->save(false);
                                }
                                //check if it is promotion
                                $promo = $settings->get('ticket', 'promotion');;
                                if ($pts->points > $promo) {
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

                        if ($i == 0) { //the first ticket, format the values
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
                            $totalMin = ($timeExp[0] * 60) + $timeExp[1];
                            $totalMinDiff = $totalMin - 60;
                            //chenge back to 13H00 Format
                            $hr = intval($totalMinDiff / 60);
                            $min = $totalMinDiff % 60;
                            $time = sprintf("%02dH%02d", $hr, $min);

                            //save SMS ready for sending
                            /*$this->db->createCommand()->insert('SMS', [
                                'ticket' => $ticketModel->id,
                                'customer' => $ticketModel->customer,
                                'route' => $ticketModel->route,
                                'dept_date' => $ticketModel->dept_date,
                                'dept_time' => $ticketModel->dept_time,
                                'message' => "Hello! Ticket:{$ticketModel->ticket}, {$ticketModel->start}-{$ticketModel->end} Seat:{$ticketModel->seat}, {$ticketModel->dept_date},{$time}. Inquiries call {$customerRoute->customer_care}. Thanks for choosing VOLCANO EXPRESS",
                                'created_at' => time(),
                                'updated_at' => time(),
                                'created_by' => Yii::$app->user->id,
                                'updated_by' => Yii::$app->user->id,
                            ])->execute();*/
                            $ticketModel->customer = 'NONE';
                        }
                    }
                    $success['message']['common']['remaining'] = $card->remaining_trips;
                    $success['message']['common']['card'] = strval('NONE');
                } else {
                    $success['success'] = false;
                    $success['message'] = json_encode($ticketModel->errors); //'Could not generate ticket';
                }
            } catch (\yii\base\Exception $e) {
                $success['success'] = false;
                $success['message'] = 'System Error occured:' . $e->getMessage();
                $ticketModel->delete();
            } catch (\yii\db\IntegrityException $e) {
                $success['success'] = false;
                $success['message'] = 'Seat already sold to someone else. Try another one!';
                $ticketModel->delete();
            }
        }

        return $success;
    }

    public function actionSellGatewayTickets($count = 1)
    {
        $settings = Yii::$app->settings;

        $success = [];

        //limit to 10
        if ($count > 10) {
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

        $card = $data['card'];
        $start = $data['start'];
        $end = $data['end'];
        $time = $data['time'];
        $routeId = $data['route'];
        $currency = $data['currency'];

        $seat = 0;
        $priceRoute = null;

        if (empty($card) || strlen($card) < 16) {
            $success['success'] = false;
            $success['message'] = 'Card is Invalid';
            return $success;
        }

        $originalDate = $data['date'];
        $origDateUnixTs = strtotime($originalDate . ' ' . str_replace('H', ':', $time));
        $date = date("Y-m-d", $origDateUnixTs);

        //check if time have passed
        $checkForBookingTime = false;

        if ($settings->has('ticket', 'check-booking-time')) {
            $checkForBookingTime = $settings->get('ticket', 'check-booking-time') == 1;
        }

        if ($origDateUnixTs < time() && $checkForBookingTime) {
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


        //find price
        //find price
        $price = $this->db->createCommand('SELECT price FROM Pricing WHERE TRIM(route)=:route AND ((TRIM(start)=:start AND TRIM(end)=:end)) AND TRIM(currency)=:currency')
            ->bindValue(':start', $start)
            ->bindValue(':end', $end)
            ->bindValue(':route', $customerRoute->id)
            ->bindValue(':currency', $currency)
            ->queryScalar();

        if (empty($price) && intval($price) != 0) {
            $success['success'] = false;
            $success['message'] = 'No Price for  Route/Currency!';
            return $success;
        }

        //get planned routes for date and hour
        $bus = null;
        $busSeats = [];

        $message = "No Bus for " . Yii::$app->formatter->asDate($date, 'dd-M-yy') . " " . $time;

        $proute = null;

        $proute = null;

        $proutes = PlannedRoute::find()->where([
            'dept_date' => $date,
            'dept_time' => $time,
            'route' => $customerRoute->id,
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
        } elseif ($proute->is_active == 0) {
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
            if ($seatCount == 0) {
                $success['success'] = false;
                //$success['message'] = "Bus {$proute->bus} is full";
                $success['message'] = "Bus is full";
                return $success;
            } elseif ($seatCount < $count) {
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
                $ticketModel->is_staff = 0;
                $ticketModel->issued_on = time();
                $ticketModel->machine_serial = $data['pos'];
                $ticketModel->currency = $currency;

                $ticketModel->is_deleted = 0;

                $ticketModel->status = Ticket::STATUS_CONFIRMED;
                $ticketModel->expired_in = 0;
                $ticketModel->discount = 0;
                $ticketModel->price = $price;

                //make Payment Request before saving Tickets
                $description = "Buy {$count} Tickets from {$start}-{$end}";
                $ticketIds = []; //for deleting in case of failure

                $config = new \app\components\GatewayConfig();
                $request = new \hosannahighertech\gateway\PaymentRequest();
                $gateway = new \hosannahighertech\gateway\Gateway($config);

                $request = $request->setCard($card)
                    ->setDescription($description)
                    ->setAmount($price)
                    ->setCurrency($currency)
                    ->setCompany(1)
                    ->setTransferTo(5990112464478798);

                $paymentReq = $gateway->sendRequest($request);
                if ($paymentReq === null) {
                    $success['success'] = false;
                    $success['message'] = $gateway->getError();
                    return $success;
                }

                //does customer exist in the database
                $customerModel = Customer::findOne($paymentReq->getOwnerMobile());

                if (empty($customerModel)) {
                    $customerModel = new Customer();
                    $customerModel->attributes = [
                        'name' => $paymentReq->getOwnerName(),
                        'mobile' => $paymentReq->getOwnerMobile()
                    ];
                    $customerModel->save();
                }

                $ticketModel->customer = $customerModel->mobile;

                if ($ticketModel->save()) {
                    for ($i = 0; $i < $count; $i++) {
                        $ticketIds[] = $ticketModel->id;
                        //increment points
                        $pstart = $ticketModel->start;
                        $pend = $ticketModel->end;

                        if ($ticketModel->status == Ticket::STATUS_CONFIRMED) {
                            if ($i > 0) { //the first model, ignore if
                                $ticketModel->isNewRecord = true;
                                $ticketModel->id = null;
                                $ticketModel->ticket = null;
                                $ticketModel->seat = $busSeats[$i];
                            }

                            $ticketModel->is_promo = 0;

                            //generate the other remaining
                            if ($customerRoute->has_promotion == 1) {
                                if ($customerRoute->is_return == 1) {
                                    //to make life easier in handling points all return routes
                                    //are converted to going route equivalent and then inserted that way
                                    //GITEGA BUJA will be added as BUJA GITEGA
                                    $pstart = $ticketModel->end;
                                    $pend = $ticketModel->start;
                                }

                                $pts = Point::findOne([
                                    'customer' => $customerModel->mobile,
                                    'start' => $pstart,
                                    'end' => $pend
                                ]);
                                if (empty($pts)) {
                                    $pts = new Point;
                                    $pts->attributes = [
                                        'customer' => $customerModel->mobile,
                                        'start' => $pstart,
                                        'end' => $pend,
                                        'points' => 1,
                                        'created_at' => time(),
                                        'updated_at' => time(),
                                    ];
                                    $pts->save();
                                } else {
                                    $pts->points = $pts->points + 1;
                                    $pts->updated_at = time();
                                    $pts->save(false);
                                }
                                //check if it is promotion
                                $promo = $settings->get('ticket', 'promotion');;
                                if ($pts->points > $promo) {
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

                        if ($i == 0) { //the first ticket, format the values
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
                            $totalMin = ($timeExp[0] * 60) + $timeExp[1];
                            $totalMinDiff = $totalMin - 60;
                            //chenge back to 13H00 Format
                            $hr = intval($totalMinDiff / 60);
                            $min = $totalMinDiff % 60;
                            $time = sprintf("%02dH%02d", $hr, $min);

                            //save SMS ready for sending
                            /*$this->db->createCommand()->insert('SMS', [
                                'ticket' => $ticketModel->id,
                                'customer' => $ticketModel->customer,
                                'route' => $ticketModel->route,
                                'dept_date' => $ticketModel->dept_date,
                                'dept_time' => $ticketModel->dept_time,
                                'message' => "Hello! Ticket:{$ticketModel->ticket}, {$ticketModel->start}-{$ticketModel->end} Seat:{$ticketModel->seat}, {$ticketModel->dept_date},{$time}. Inquiries call {$customerRoute->customer_care}. Thanks for choosing VOLCANO EXPRESS",
                                'created_at' => time(),
                                'updated_at' => time(),
                                'created_by' => Yii::$app->user->id,
                                'updated_by' => Yii::$app->user->id,
                            ])->execute();*/
                        }
                    }
                    $receipt = $gateway->confirmPayment($paymentReq->getReceipt());
                    if ($receipt === false) {
                        //delete Tickets
                        Ticket::deleteAll(['id' => $ticketIds]);
                        $success['success'] = false;
                        $success['message'] = $gateway->getError();
                        return $success;
                    } else {
                        $success['message']['common']['receipt'] = $receipt->getReceipt();
                        $success['message']['common']['balance'] = number_format($receipt->getBalance());
                        $success['message']['common']['card'] = implode('-', str_split($card, 4));
                    }
                } else {
                    $success['success'] = false;
                    $success['message'] = json_encode($ticketModel->errors); //'Could not generate ticket';
                }
            } catch (\yii\base\Exception $e) {
                $success['success'] = false;
                $success['message'] = 'System Error occured:' . $e->getMessage();
                $ticketModel->delete();
            } catch (\yii\db\IntegrityException $e) {
                $success['success'] = false;
                $success['message'] = 'Seat already sold to someone else. Try another one!';
                $ticketModel->delete();
            }
        }

        return $success;
    }

    public function actionPosReport($id, $start_date, $end_date)
    {
        $msg = $this->isAllowed($id);
        if (is_array($msg)) {
            return $msg;
        }

        $msg = $this->isAllowed($id);
        if (is_array($msg)) {
            return $msg;
        }

        $sqlCards = 'SELECT IFNULL(SUM(price*total_trips), 0) AS revenue, COUNT(card) AS cards, currency AS currency FROM RouteCards WHERE DATE(FROM_UNIXTIME(sold_at)) BETWEEN :start AND :end AND pos=:pos GROUP BY currency';

        $sqlTickets = 'SELECT IFNULL(SUM(price-discount), 0) AS revenue,currency, COUNT(ticket) AS tickets FROM Tickets WHERE (status = \'CO\' OR status = \'CT\') AND DATE(FROM_UNIXTIME(updated_at)) BETWEEN :start AND :end AND machine_serial=:pos GROUP BY currency';

        $tickets =  $this->db->createCommand($sqlTickets)
            ->bindValue(':start', $start_date)
            ->bindValue(':end', $end_date)
            ->bindValue(':pos', $id)
            ->queryAll();

        $cards =  $this->db->createCommand($sqlCards)
            ->bindValue(':start', $start_date)
            ->bindValue(':end', $end_date)
            ->bindValue(':pos', $id)
            ->queryAll();

        $output = [];
        $totalTickets = 0;
        $totalCards = 0;
        $totalRevenue = ['FIB' => 0, 'RWF' => 0, 'UGS' => 0, 'USD' => 0];
        $output = [];

        if (is_array($cards)) {
            foreach ($cards as $revenue) {
                if (empty($revenue['currency'])) {
                    continue;
                }

                $currency = $revenue['currency'];

                $totalCards = $totalCards + $revenue['cards'];
                $totalRevenue[$currency] = $totalRevenue[$currency] + $revenue['revenue'];

                $output[] = implode(' ', [number_format($revenue['revenue']) . ' ' . $currency . ', ', $revenue['cards'] . ' Cards']);
            }
        }

        if (is_array($tickets)) {
            foreach ($tickets as $revenue) {
                if (empty($revenue['currency'])) {
                    continue;
                }

                $currency = $revenue['currency'];

                $totalTickets = $totalTickets + $revenue['tickets'];
                $totalRevenue[$currency] = $totalRevenue[$currency] + $revenue['revenue'];

                $output[] = implode(' ', [number_format($revenue['revenue']) . ' ' . $currency . ', ', $revenue['tickets'] . ' Tickets']);
            }
        }

        array_walk($totalRevenue, function (&$val, $key) {
            $val =  "$val $key";
        });

        $output[] = 'Total Tickets:' . $totalTickets;
        $output[] = 'Total Cards Sold:' . $totalCards;
        $output[] = 'Total Revenue:;-------------;' . implode(', ', array_values($totalRevenue)) . ';-------------;';
        $output[] = 'Created:' . Yii::$app->formatter->asDateTime(time(), 'php:d-m-Y H:i') . ';-------------;';
        //total revenue
        //; will be converted to new line
        return ";$id;-------------------------;" . implode(';', $output);
    }

    public function actionUserReport($pos, $start_date, $end_date)
    {
        $msg = $this->isAllowed($pos);
        if (is_array($msg)) {
            return $msg;
        }

        $id = Yii::$app->user->id;
        $name = Yii::$app->user->identity->name;

        $sqlCards = 'SELECT IFNULL(SUM(price*total_trips), 0) AS revenue, COUNT(card) AS cards, currency AS currency FROM RouteCards WHERE DATE(FROM_UNIXTIME(sold_at)) BETWEEN :start AND :end AND sold_by=:user GROUP BY currency';

        $sqlTickets = 'SELECT IFNULL(SUM(price-discount), 0) AS revenue,currency, COUNT(ticket) AS tickets FROM Tickets WHERE  (status = \'CO\' OR status = \'CT\') AND DATE(FROM_UNIXTIME(updated_at)) BETWEEN :start AND :end AND updated_by=:user GROUP BY currency';

        $tickets =  $this->db->createCommand($sqlTickets)
            ->bindValue(':start', $start_date)
            ->bindValue(':end', $end_date)
            ->bindValue(':user', $id)
            ->queryAll();

        $cards =  $this->db->createCommand($sqlCards)
            ->bindValue(':start', $start_date)
            ->bindValue(':end', $end_date)
            ->bindValue(':user', $id)
            ->queryAll();

        $output = [];
        $totalTickets = 0;
        $totalCards = 0;
        $totalRevenue = ['FIB' => 0, 'RWF' => 0, 'UGS' => 0, 'USD' => 0];
        $output = [];

        if (is_array($cards)) {
            foreach ($cards as $revenue) {
                if (empty($revenue['currency'])) {
                    continue;
                }

                $currency = $revenue['currency'];

                $totalCards = $totalCards + $revenue['cards'];
                $totalRevenue[$currency] = $totalRevenue[$currency] + $revenue['revenue'];

                $output[] = implode(' ', [number_format($revenue['revenue']) . ' ' . $currency . ', ', $revenue['cards'] . ' Cards']);
            }
        }

        if (is_array($tickets)) {
            foreach ($tickets as $revenue) {
                if (empty($revenue['currency'])) {
                    continue;
                }

                $currency = $revenue['currency'];

                $totalTickets = $totalTickets + $revenue['tickets'];
                $totalRevenue[$currency] = $totalRevenue[$currency] + $revenue['revenue'];

                $output[] = implode(' ', [number_format($revenue['revenue']) . ' ' . $currency . ', ', $revenue['tickets'] . ' Tickets']);
            }
        }

        array_walk($totalRevenue, function (&$val, $key) {
            $val =  "$val $key";
        });

        $output[] = 'Total Tickets:' . $totalTickets;
        $output[] = 'Total Cards Sold:' . $totalCards;
        $output[] = 'Total Revenue:;-------------;' . implode(', ', array_values($totalRevenue)) . ';-------------;';
        $output[] = 'Created:' . Yii::$app->formatter->asDateTime(time(), 'php:d-m-Y H:i') . ';-------------;';
        //total revenue
        //; will be converted to new line
        return ";$name - $id;----------------------;" . implode(';', $output);
    }

    public function actionFreeSeats($detailed = 0)
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

        $originalDate = $data['date'];
        $origDateUnixTs = strtotime($originalDate . ' ' . str_replace('H', ':', $time));
        $date = date("Y-m-d", $origDateUnixTs);

        //check if time have passed //TDL disable for VBurundi only
        /*if($origDateUnixTs<time())
        {
            $success['success'] = false;
            $success['message'] = 'Booking Time Passed!';
            return $success;
        }*/

        //get planned routes for date and hour
        $customerRoute = null;
        $bus = null;

        $message = "No Bus for " . Yii::$app->formatter->asDate($date, 'dd-M-yy') . " " . $time;

        $customerRoute = Route::findOne($route);
        if (empty($customerRoute)) {
            $success['success'] = false;
            $success['message'] = 'Route is not valid';
            return $success;
        }

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
            'dept_date' => $date,
            'dept_time' => $time,
            'route' => $customerRoute->id,
        ])->orderBy('priority ASC')->one();

        //no route found
        if (empty($proute)) {
            $success['success'] = false;
            $success['message'] = 'No Bus for that Time!';
            return $success;
        }

        if ($detailed == 1) {
            $details = $this->getSeatDetails($proute, $start, $end);
            $success['success'] = true;
            $success['message'] = $details;
            return $success;
        }

        $bus = Bus::findOne($proute->bus);

        //check if bus is not full
        if (!$this->isBusFull($customerRoute->id, $proute->capacity, $date, $time, $start, $end, $bus->regno)) {
            //get free seats
            $busSeats = $this->getFreeSeats($customerRoute->id, $start, $end, $date, $time, $proute->capacity, $bus->regno);
            $success['message'] = "Bus {$proute->bus} of {$proute->dept_date} {$proute->dept_time} FREE SEATS:" . ($customerRoute->is_intl == 1 ? implode(',', $busSeats) : 'Remaining:' . count($busSeats));
            $success['success'] = true;
            return $success;
        }

        $success['success'] = false;
        $success['message'] = 'Bus is full';
        return $success;
    }

    public function actionFreeSeatsv2()
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

        $originalDate = $data['date'];
        $origDateUnixTs = strtotime($originalDate . ' ' . str_replace('H', ':', $time));
        $date = date("Y-m-d", $origDateUnixTs);

        //get planned routes for date and hour
        $customerRoute = null;
        $bus = null;

        $message = "No Bus for " . Yii::$app->formatter->asDate($date, 'dd-M-yy') . " " . $time;

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
            'dept_date' => $date,
            'dept_time' => $time,
            'route' => $customerRoute->id,
        ])->orderBy('priority ASC')->one();

        //no route found
        if (empty($proute)) {
            $success['success'] = false;
            $success['message'] = 'No Bus for that Time!';
            return $success;
        }

        $bus = Bus::findOne($proute->bus);

        //check if bus is not full
        if (!$this->isBusFull($customerRoute->id, $proute->capacity, $date, $time, $start, $end, $bus->regno)) {
            //get free seats
            $busSeats = $this->getFreeSeats($customerRoute->id, $start, $end, $date, $time, $proute->capacity, $bus->regno);
            $success['message'] = "";
            $success['seat-data'] = [
                'bus' => "Bus {$proute->bus} of {$proute->dept_date} {$proute->dept_time}",
                'is_intl' => $customerRoute->is_intl == 1,
                'seats' => $customerRoute->is_intl == 0 ? [count($busSeats)] : $busSeats
            ];
            $success['success'] = true;
            return $success;
        }

        $success['success'] = false;
        $success['message'] = 'Bus is full';
        return $success;
    }




    public function actionCancelSeat()
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
        $booking = $data['booking'];

        $ticket =  Ticket::find()->where(['ticket' => $booking])->one();
        if (empty($ticket)) {
            $success['success'] = false;
            $success['message'] = 'Booking invalid or not found';
            return $success;
        }
        //available so cancel it
        if ($ticket->delete()) {
            $success['success'] = true;
            $success['message'] = 'Booking cancelled';
            return $success;
        } else {
            $success['success'] = false;
            $success['message'] = 'Booking could not be cancelled';
            return $success;
        }
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
            if ($occupied >= $busCapacity) {
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

    protected function getFreeSeats($routeId, $start, $end, $date, $time, $busCapacity, $busReg)
    {
        //Yii::error(func_get_args());
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
        foreach ($routes as $r) {
            //Yii::error($r);
            $this->db->createCommand('UPDATE ReservedSeats SET status = "EX", seat = seat*-1 WHERE start=:start AND end=:end AND route=:route AND dept_date=:date AND dept_time = :time AND bus=:bus AND ((status = "BO" AND  (UNIX_TIMESTAMP(CONCAT(dept_date, " ", REPLACE(dept_time, "H", ":"), ":00"))- expires_in)<=UNIX_TIMESTAMP(CONVERT_TZ(NOW(), "Africa/Kigali",  "' . Yii::$app->user->identity->timezone . '"))) OR (status = "BT" AND  (issued_on + expires_in) <= UNIX_TIMESTAMP(CONVERT_TZ(NOW(), "Africa/Kigali",  "' . Yii::$app->user->identity->timezone . '"))))')
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
            ->where(['route' => $routeId, 'dept_date' => $date, 'dept_time' => $time, 'bus' => $busReg])
            ->andWhere([
                'OR',
                new \yii\db\Expression("(status='BO' OR status='BT') AND (UNIX_TIMESTAMP(CONCAT(dept_date, ' ', REPLACE(dept_time, 'H', ':'), ':00'))- expires_in)>UNIX_TIMESTAMP(CONVERT_TZ(NOW(), 'Africa/Kigali',  '" . Yii::$app->user->identity->timezone . "'))"),
                ['status' => Ticket::STATUS_CONFIRMED],
                ['status' => Ticket::STATUS_CARD_TICKET]
            ]);
        //add reotes
        $where = [];
        $where[] = 'OR';
        $where[] = ['start' => $start, 'end' => $end];
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
        foreach ($routes as $r) {
            $this->db->createCommand('UPDATE ReservedSeats SET status = "EX", seat = seat*-1 WHERE start=:start AND end=:end AND route=:route AND dept_date=:date AND dept_time = :time AND bus=:bus AND ((status = "BO" AND  (UNIX_TIMESTAMP(CONCAT(dept_date, " ", REPLACE(dept_time, "H", ":"), ":00"))- expires_in)<=UNIX_TIMESTAMP(CONVERT_TZ(NOW(), "Africa/Kigali",  "' . Yii::$app->user->identity->timezone . '"))) OR (status = "BT" AND  (issued_on + expires_in) <= UNIX_TIMESTAMP(CONVERT_TZ(NOW(), "Africa/Kigali",  "' . Yii::$app->user->identity->timezone . '"))))')
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
            ->where(['route' => $proute->route, 'dept_date' => $proute->dept_date, 'dept_time' => $proute->dept_time, 'bus' => $proute->bus])
            ->andWhere([
                'OR',
                new \yii\db\Expression("(status='BO' OR status='BT') AND (UNIX_TIMESTAMP(CONCAT(dept_date, ' ', REPLACE(dept_time, 'H', ':'), ':00'))- expires_in)>UNIX_TIMESTAMP(CONVERT_TZ(NOW(),  '" . Yii::$app->user->identity->timezone . "', 'Africa/Kigali'))"),
                ['status' => Ticket::STATUS_CONFIRMED],
                ['status' => Ticket::STATUS_CARD_TICKET]
            ]);
        //add reotes
        $where = [];
        $where[] = 'OR';
        $where[] = ['start' => $start, 'end' => $end];
        foreach ($routes as $route) {
            $where[] = $route;
        }
        $seatsQuery->andWhere(['or', $where])
            ->groupBy(['seat', 'status']);

        $booked = $seatsQuery->createCommand($this->db)->queryAll();

        $free = [];
        $allSeats = [];

        $occupiedSeatNoStatus = [];

        foreach ($booked as $bookedOne) {
            $allSeats[] = $bookedOne;
            $occupiedSeatNoStatus[] = $bookedOne['seat'];
        }
        //get free seats only
        $free = array_values(array_diff($seats, $occupiedSeatNoStatus));

        //add keys
        array_walk($free, function ($value) use (&$allSeats) {
            $allSeats[] = [
                'seat' => $value,
                'status' => Ticket::STATUS_FREE,
            ];
        });

        $details['seats'] = $allSeats;
        return $details;
    }
    protected function formatTicketOld($ticketModel, $customerModel = null, $isCommon = false)
    {
        if ($customerModel == null) {
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
        if ($boardingOffset > 0) {
            //change hours to minutes
            $timeExp = explode('H', $time);
            $totalMin = ($timeExp[0] * 60) + $timeExp[1];
            $totalMinDiff = $totalMin + $boardingOffset;
            //chenge back to 13H00 Format
            $hr = intval($totalMinDiff / 60);
            $min = $totalMinDiff % 60;
            $time = sprintf("%02dH%02d", $hr, $min);
        }

        $ticket['route'] = empty($route->parentR) ? $route->name : $route->parentR->name; //use parent route only
        $ticket['journey'] = "{$ticketModel->start} - {$ticketModel->end} ";
        $ticket['date'] = $ticketModel->dept_date;
        $ticket['time'] = $time;
        $ticket['pos'] = $ticketModel->machine_serial;
        $ticket['ticket_serial'] = $ticketModel->serial_number;
        //$ticket['name'] = strlen($customerModel->mobile)>12 ? $customerModel->name : $customerModel->name.' - '.$ticketModel->customer; //$customerModel->name.' - ***'.(substr($ticketModel->customer, 7));

        $setting = Yii::$app->settings;

        if ($setting->has('ticket', 'hidecode') && $setting->get('ticket', 'hidecode') == 1) {
            if (strlen($customerModel->mobile) > 12) {
                $ticket['name'] = strlen($customerModel->mobile) > 12 ? $customerModel->name : $customerModel->name . ' - ' . $ticketModel->customer;
            } //$customerModel->name.' - ***'.(substr($ticketModel->customer, 7));
            else {
                $ticket['name'] = strlen($customerModel->mobile) < 6 ? $customerModel->name : $customerModel->name . ' - ' . $ticketModel->customer;
            } //$customerModel->name.' - ***'.(substr($ticketModel->customer, 7));
        } else {
            $ticket['name'] = strlen($customerModel->mobile) > 12 ? $customerModel->name : $customerModel->name . ' - ' . $ticketModel->customer;
        } //$customerModel->name.' - ***'.(substr($ticketModel->customer, 7));

        if ($setting->has('ticket', 'hide')) {
            if ($setting->get('ticket', 'hide') == 1) {
                if ($ticketModel->route == 1) {
                    if ($ticketModel->end == 'MUHANGA') {
                        $ticket['journey'] = "{$ticketModel->start} - MUHANGA ";
                    } else {
                        $ticket['journey'] = "{$ticketModel->start} - NGORORERO\n stop : ({$ticketModel->end})";
                    }
                }
            }
        }

        if ($setting->has('ticket', 'print-bus-plate') && $setting->get('ticket', 'print-bus-plate') == 1) {
            $ticket['route'] = "{$ticket['route']}\nBus: {$ticketModel->bus}";
        } else {
            $ticket['route'] = "{$ticket['route']}\nBus: NOT SET";
        }

        if ($setting->has('ticket', 'print-cashier-name') && $setting->get('ticket', 'print-cashier-name') == 1) {
            $time = Yii::$app->formatter->asDatetime(time(), 'short');
            $user = Yii::$app->user->identity;
            $ticket['generated'] = "{$time}\nCashier: {$user->name}";
        } else {
            $ticket['generated'] = Yii::$app->formatter->asDatetime(time(), 'short');
        }

        $ticket['discount'] = $ticketModel->discount;

        if ($ticketModel->status == Ticket::STATUS_CONFIRMED || $ticketModel->status == Ticket::STATUS_CARD_TICKET) {
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
            $ticket['is_staff'] = is_null($ticketModel->is_staff) ? 0 : $ticketModel->is_staff;
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
            $deptTimeUnix = strtotime($ticketModel->dept_date . ' ' . str_replace('H', ':', $ticketModel->dept_time));
            $expiryTime = $deptTimeUnix - $ticketModel->expired_in;

            $ticket['expires'] = Yii::$app->formatter->asDatetime($expiryTime, 'short');;
        }
        //all need currency
        $ticket['currency'] = $ticketModel->currency;
        $ticket['bus'] = $ticketModel->bus;
        $ticket['show_plate'] = Yii::$app->user->identity->tenant_db != 'volcano_rwanda';

        return $ticket;
    }

    protected function formatTicket($ticketModel, $customerModel = null, $isCommon = false)
    {
        if ($customerModel == null) {
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

        $boardingOffset =  $this->db->createCommand('SELECT `offset` FROM BoardingTimes WHERE `route`=:route AND `start`=:start AND `end`=:end', [
            ':route' => $ticketModel->route,
            ':start' => $ticketModel->start,
            ':end' => $ticketModel->end
        ])->queryScalar();

        if (empty($boardingOffset)) {
            $boardingOffset = 0;
        }

        //set time
        $time = $ticketModel->dept_time;
        if ($boardingOffset > 0) {
            //change hours to minutes
            $timeExp = explode('H', $time);
            $totalMin = ($timeExp[0] * 60) + $timeExp[1];
            $totalMinDiff = $totalMin + $boardingOffset;
            //chenge back to 13H00 Format
            $hr = intval($totalMinDiff / 60);
            $min = $totalMinDiff % 60;
            $time = sprintf("%02dH%02d", $hr, $min);
        }

        $ticket['route'] = empty($route->parentR) ? $route->name : $route->parentR->name; //use parent route only
        $ticket['journey'] = "{$ticketModel->start} - {$ticketModel->end} ";
        $ticket['date'] = $ticketModel->dept_date;
        $ticket['time'] = $time;
        $ticket['pos'] = $ticketModel->machine_serial;
        $ticket['ticket_serial'] = $ticketModel->serial_number;
        //$ticket['name'] = strlen($customerModel->mobile)>12 ? $customerModel->name : $customerModel->name.' - '.$ticketModel->customer; //$customerModel->name.' - ***'.(substr($ticketModel->customer, 7));

        $setting = Yii::$app->settings;

        if ($setting->has('ticket', 'hidecode') && $setting->get('ticket', 'hidecode') == 1) {
            if (strlen($customerModel->mobile) > 12) {
                $ticket['name'] = strlen($customerModel->mobile) > 12 ? $customerModel->name : $customerModel->name . ' - ' . $ticketModel->customer;
            } //$customerModel->name.' - ***'.(substr($ticketModel->customer, 7));
            else {
                $ticket['name'] = strlen($customerModel->mobile) < 6 ? $customerModel->name : $customerModel->name . ' - ' . $ticketModel->customer;
            } //$customerModel->name.' - ***'.(substr($ticketModel->customer, 7));
        } else {
            $ticket['name'] = strlen($customerModel->mobile) > 12 ? $customerModel->name : $customerModel->name . ' - ' . $ticketModel->customer;
        } //$customerModel->name.' - ***'.(substr($ticketModel->customer, 7));

        if ($setting->has('ticket', 'hide')) {
            if ($setting->get('ticket', 'hide') == 1) {
                if ($ticketModel->route == 1) {
                    if ($ticketModel->end == 'MUHANGA') {
                        $ticket['journey'] = "{$ticketModel->start} - MUHANGA ";
                    } else {
                        $ticket['journey'] = "{$ticketModel->start} - NGORORERO\n stop : ({$ticketModel->end})";
                    }
                }
            }
        }

        if ($setting->has('ticket', 'print-bus-plate') && $setting->get('ticket', 'print-bus-plate') == 1) {
            $ticket['route'] = "{$ticket['route']}\nBus: {$ticketModel->bus}";
        } else {
            $ticket['route'] = "{$ticket['route']}\nBus: NOT SET";
        }

        if ($setting->has('ticket', 'print-cashier-name') && $setting->get('ticket', 'print-cashier-name') == 1) {
            $time = Yii::$app->formatter->asDatetime(time(), 'short');
            $user = Yii::$app->user->identity;
            $ticket['generated'] = "{$time}\nCashier: {$user->name}";
        } else {
            $ticket['generated'] = Yii::$app->formatter->asDatetime(time(), 'short');
        }

        $ticket['discount'] = $ticketModel->discount;

        if ($ticketModel->status == Ticket::STATUS_CONFIRMED || $ticketModel->status == Ticket::STATUS_CARD_TICKET) {
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
            $ticket['is_staff'] = is_null($ticketModel->is_staff) ? 0 : $ticketModel->is_staff;
            $ticket['is_intl'] = $route->is_intl;
            //customer details for intl route
            if ($route->is_intl) {
                $ticket['passport'] = $ticketModel['doc_number'];
                $ticket['nationality'] = $customerModel->nationality;
                $ticket['from_nation'] = $ticketModel['from_country'];
                $ticket['to_nation'] = $ticketModel['to_country'];
                $ticket['gender'] = $customerModel->gender == 1 ? 'Male' : 'Female';
            }
        } elseif ($ticketModel->status == Ticket::STATUS_BOOKED) {
            $deptTimeUnix = strtotime($ticketModel->dept_date . ' ' . str_replace('H', ':', $ticketModel->dept_time));
            $expiryTime = $deptTimeUnix - $ticketModel->expired_in;

            $ticket['expires'] = Yii::$app->formatter->asDatetime($expiryTime, 'short');;
        }
        //all need currency
        $ticket['currency'] = $ticketModel->currency;
        $ticket['bus'] = $ticketModel->bus;
        $ticket['show_plate'] = Yii::$app->user->identity->tenant_db != 'volcano_rwanda';

        return $ticket;
    }

    public function actionManifest()
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

        $dbman = null;
        $settings = Yii::$app->settings;
        if ($settings->has('ticket', 'nomanifest')) {
            if ($settings->get('ticket', 'nomanifest') == 1) {
                $dbman = 1;
            }
        }
        if ($dbman == 1) {
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
        $origDateUnixTs = strtotime($date . ' ' . str_replace('H', ':', $time));
        $date = date("Y-m-d", $origDateUnixTs);


        //get planned routes for date and hour
        $customerRoute = null;
        $bus = null;

        $message = "No Bus for " . Yii::$app->formatter->asDate($date, 'dd-M-yy') . " " . $time;

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
            'dept_date' => $date,
            'dept_time' => $time,
            'route' => $customerRoute->id,
        ])->orderBy('priority ASC')->one();

        //no route found
        if (empty($proute)) {
            $success['success'] = false;
            $success['message'] = 'No Bus for that Time!';
            return $success;
        }

        $bus = Bus::findOne($proute->bus);
        $dbase = null;
        $settings = Yii::$app->settings;
        if ($settings->has('ticket', 'manifest')) {
            if ($settings->get('ticket', 'manifest') == 1) {
                $dbase = 1;
            }
        }
        if ($dbase == 1) {
            //$customersSQL = 'SELECT CONCAT(name, ", ", price) as customer  FROM Tickets t INNER JOIN Customers c ON c.mobile = t.customer WHERE t.is_deleted=0 AND t.updated_by=:updated_by and t.dept_date=:dept_date AND dept_time=:dept_time AND route=:route AND bus = :bus';
            $customersSQL = 'SELECT CONCAT(name, ", ", price) as customer  FROM Tickets t INNER JOIN Customers c ON c.mobile = t.customer WHERE t.is_deleted=0 AND t.dept_date=:dept_date AND dept_time=:dept_time AND route=:route AND bus = :bus';

            $customers = $this->db->createCommand($customersSQL)
                ->bindValue(':dept_date', $proute->dept_date)
                ->bindValue(':dept_time', $proute->dept_time)
                ->bindValue(':route', $proute->route)
                ->bindValue(':bus', $proute->bus)
                //->bindValue(':updated_by', Yii::$app->user->id)
                ->queryColumn();

            // $revenueSQL = 'SELECT CONCAT(currency, "  ", SUM(price-discount),", Total Tickets:",COUNT(id) ,",     Cashier: ",updated_by) as revenue FROM Tickets WHERE is_deleted=0 AND updated_by=:updated_by and dept_date=:dept_date AND dept_time=:dept_time AND route=:route AND bus = :bus GROUP BY currency';

            //$revenueSQL = 'SELECT CONCAT(currency, "  ", SUM(price-discount),", Total Tickets:",COUNT(id) ,",     Cashier: ",s.name," (",s.location,")") as revenue FROM Tickets t INNER JOIN volcano_shared.Staffs s ON t.updated_by=s.mobile WHERE t.updated_by=:updated_by and dept_date=:dept_date AND dept_time=:dept_time AND route=:route AND bus = :bus GROUP BY currency';
            $revenueSQL = 'SELECT CONCAT(currency, "  ", SUM(price-discount),", Total Tickets:",COUNT(id)) as revenue FROM Tickets t INNER JOIN volcano_shared.Staffs s ON t.updated_by=s.mobile WHERE dept_date=:dept_date AND dept_time=:dept_time AND route=:route AND bus = :bus GROUP BY currency';
            $revenue = $this->db->createCommand($revenueSQL)
                ->bindValue(':dept_date', $proute->dept_date)
                ->bindValue(':dept_time', $proute->dept_time)
                ->bindValue(':route', $proute->route)
                ->bindValue(':bus', $proute->bus)
                //->bindValue(':updated_by', Yii::$app->user->id)

                ->queryColumn();

            $success['success'] = true;
            $success['message'] = [
                'bus' => $bus->regno,
                'dept_date' => $data['date'],
                'dept_time' => $time,
                'route' => $customerRoute->name,
                'customers' => $customers,
                'revenue' => $revenue,
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
                'bus' => $bus->regno,
                'dept_date' => $data['date'],
                'dept_time' => $time,
                'route' => $customerRoute->name,
                'customers' => $customers,
                'revenue' => $revenue,
            ];
            return $success;
        }
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

        return $found > 0 && $checkStaffs > 0;
    }

    private function isAllowed($pos)
    {
        Yii::error($pos);
        $posModel = POS::findOne($pos);
        if (empty($posModel)) {
            $success['success'] = false;
            $success['message'] = "POS Not found: {$pos}";
            return $success;
        } elseif ($posModel->is_active == 0) {
            $success['success'] = false;
            $success['message'] = 'POS Suspended';
            return $success;
        }
        //check if staff selling is an agent and that he is not suspended
        $staff = Yii::$app->user->identity;
        if ($staff->is_active == 0) {
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
            empty($customerModel->dob) ||
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

    public function actionTicketsFromQr($qr, $pos)
    {
        $msg = $this->isAllowed($pos);
        if (is_array($msg)) {
            return $msg;
        }

        $success = [];
        $qrDetails = json_decode($qr, true);

        if (empty($qrDetails)) {
            $success['success'] = false;
            $success['message'] = 'Invalid Details';
            return $success;
        }

        //find ticket
        $ticket = Ticket::find()->where(['ticket' => $qrDetails['t']])->one();
        if (empty($ticket)) {
            $success['success'] = false;
            $success['message'] = "Ticket {$qrDetails['t']} not found";
            return $success;
        }

        if ($ticket->is_printed == 1) {
            $success['success'] = false;
            $success['message'] = "Ticket {$qrDetails['t']} is already printed";
            return $success;
        }

        //verify
        $hash = hash('SHA256', $ticket->created_at . '0' . $ticket->created_by . '0' . $ticket->id);
        if ($hash != $qrDetails['v']) {
            $success['success'] = false;
            $success['message'] = "Ticket {$qrDetails['t']} is tampered";
            return $success;
        }

        $success['success'] = true;
        $success['message'] = $this->formatTicket($ticket);

        //mark it printed
        Ticket::updateAll(['updated_by' => '1', 'is_printed' => 1], ['id' => $ticket->id]);
        return $success;
    }

    public function actionTicketPrinting($qr, $pos)
    {
        $msg = $this->isAllowed($pos);
        if (is_array($msg)) {
            return $msg;
        }

        $success = [];


        //find ticket
        $ticket = Ticket::find()->where(['ticket' => $qr])->one();

        if (empty($ticket)) {
            $success['success'] = false;
            $success['message'] = "Ticket {$qr} not found";
            return $success;
        }

        if ($ticket->is_printed == 1) {
            $success['success'] = false;
            $success['message'] = "Ticket {$qr} is already printed";
            return $success;
        }

        //verify



        $success['success'] = true;
        $success['message'] = $this->formatTicket($ticket);

        //mark it printed
        Ticket::updateAll(['is_printed' => 1], ['id' => $ticket->id]);
        return $success;
    }
}
