<?php

namespace app\modules\v1\controllers;

use app\models\Bus;
use yii\data\ActiveDataProvider;
use app\models\Pricing;
use app\models\PlannedRoute;
use app\models\Route;
use app\models\Stop;
use app\models\Ticket;
use Yii;
use yii\helpers\ArrayHelper;

class RoutingController extends ApiController
{
    public $modelClass = 'app\models\Route';

    public function actionStations($country = 1, $key = '')
    {
        if ($country == 1) {
            return new ActiveDataProvider([
                'query' => Stop::find()->select(['country', 'name'])
            ]);
        } else {
            $stops =  Stop::find()
                ->select(['name'])
                ->where(['LIKE', 'name', $key])
                ->orderBy('name ASC')
                ->limit(50)
                ->asArray()
                ->all();

            return ArrayHelper::getColumn($stops, 'name');
        }
    }

    public function actionPrices($start, $end, $route)
    {
        return new ActiveDataProvider([
            'query' => Pricing::find()->select(['price', 'currency'])->where(['start' => $start, 'end' => $end, 'route' => $route])
        ]);
    }

    public function actionRoutes($parent = null)
    {
        $query = Route::find()
            //->with('children')
            ->select(['id', 'start', 'end', 'parent'])
            ->where(['parent' => $parent]);

        return new ActiveDataProvider([
            'query' => $query
        ]);
    }

    public function actionPlanned($date = null, $time = null)
    {
        $query = PlannedRoute::find()
            ->select([
                'route',
                'bus',
                'dept_date',
                'dept_time',
                'is_active',
                'capacity'
            ]);

        if (empty($date) && empty($time))
            $query->andWhere(['dept_date' => date('Y-m-d')]);

        if (!empty($date))
            $query->andWhere(['dept_date' => $date]);

        if (!empty($time))
            $query->andWhere(['dept_time' => $time]);

        return new ActiveDataProvider([
            'query' => $query
        ]);
    }

    public function actionHours($date = null, $time = null, $route = null)
    {
        //$success[]=null;
        //$success[]=null;
        //$time=SUBSTR($time,0,2);
        //$time2=$time+1;


        $revenueSQL = 'SELECT dept_time,bus,route as route_id,name as route_name FROM PlannedRoutes p Join Routes r on p.route=r.id WHERE dept_date=:date and route=:route and is_active=1';
        $revenue = $this->db->createCommand($revenueSQL)

            ->bindValues(['date' => $date])
            //->bindValues(['time' => $time.'%'])
            ->bindValues(['route' => $route])

            ->queryAll();


        if (empty($revenue)) {
            $success['success'] = false;
            $success['name'] = 'No trips planned yet';
            return $success;
        }
        $success['success'] = true;
        $success['name'] = 'Available deperture hours';

        $success['message'] = $revenue;
        return $success;
    }

    public function actionDepartures()
    {
        //$success[]=null;
        $revenueSQL = 'SELECT distinct(start) as departure FROM Pricing UNION SELECT distinct(end) as departure FROM Pricing';
        $revenue = $this->db->createCommand($revenueSQL)
            ->queryAll();

        if (empty($revenue)) {
            $success['success'] = false;
            $success['message'] = 'No stops found';
            return $success;
        }

        $success['success'] = true;
        $success['message'] = $revenue;
        return $success;
    }

    public function actionDestinations($departure = null)
    {
        //$success[]=null;
        $revenueSQL = 'SELECT start as destination FROM Pricing where end=:start union SELECT end as destination FROM Pricing where  start=:start';
        $revenue = $this->db->createCommand($revenueSQL)

            ->bindValues(['start' => $departure])

            ->queryAll();
        if (empty($revenue)) {
            $success['success'] = false;
            $success['message'] = 'No destination found';
            return $success;
        }
        $success['success'] = true;
        $success['message'] = $revenue;
        return $success;
    }

    public function actionRoute($departure = null, $destination = null)
    {
        //$success[]=null;
        $revenueSQL = 'SELECT (case when parent>0 then parent else id end) as route,name FROM Routes WHERE start=:start AND end=:end';
        $revenue = $this->db->createCommand($revenueSQL)

            ->bindValues(['start' => $departure])
            ->bindValues(['end' => $destination])
            ->queryAll();
        $success['success'] = true;
        $success['message'] = $revenue;
        return $success;
    }

    public function actionPricing($departure = null, $destination = null, $route = null)
    {
        //$success[]=null;
        $revenueSQL = 'SELECT price,currency FROM Pricing WHERE start=:start AND end=:end AND route=:route';
        $revenue = $this->db->createCommand($revenueSQL)

            ->bindValues(['start' => $departure])
            ->bindValues(['end' => $destination])
            ->bindValues(['route' => $route])
            ->queryAll();


        if (empty($revenue)) {
            $revenueSQL = 'SELECT price,currency FROM Pricing WHERE start=:end AND end=:start AND route=:route';
            $revenue = $this->db->createCommand($revenueSQL)
                ->bindValues(['start' => $departure])
                ->bindValues(['end' => $destination])
                ->bindValues(['route' => $route])
                ->queryAll();
        }

        $success['success'] = true;
        $success['message'] = $revenue;
        return $success;
    }

    public function actionBuses()
    {
        return new ActiveDataProvider([
            'query' => Bus::find()
        ]);
    }

    public function actionCheckOrCreateRoute()
    {
        $post = Yii::$app->request->post();
        $attributes = [
            'route' => $post['route'],
            'bus' => $post['bus'],
            'dept_date' => $post['dept_date'],
            'dept_time' => $post['dept_time'],
        ];
        $model = PlannedRoute::find()->where($attributes)->one();
        if (empty($model)) {
            //doe not exist, create it
            $model = new PlannedRoute($attributes);
            $model->is_active = true;
            $model->capacity = $post['bus_capacity'];
            if (!$model->save()) {
                Yii::error($model->errors);
                return [
                    'success' => false,
                    'message' => Yii::t('app', 'Could not create new Plan, Please try again!')
                ];
            }
        }
        if (!$model->is_active) {
            return [
                'success' => false,
                'message' => Yii::t('app', 'Plan exists but is suspended. Please activate it on the Portal!')
            ];
        }
        return [
            'success' => true,
            'message' => Yii::t('app', 'Plan is okay, you can start selling tickets!')
        ];
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
}
