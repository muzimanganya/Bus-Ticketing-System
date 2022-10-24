<?php
namespace app\models;
 
use Yii;
use yii\base\Model; 
use yii\data\ActiveDataProvider;
use app\models\Ticket;

/**
 * TicketSearch represents the model behind the search form about `app\models\Ticket`.
 */
class TicketSearch extends Ticket
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'route', 'issued_on', 'price', 'discount', 'seat', 'is_deleted', 'is_promo', 'is_staff', 'expired_in', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['customer'], 'string', 'max' => 256],
            [['ticket', 'bus', 'start', 'end', 'dept_date', 'dept_time', 'machine_serial', 'currency', 'status'], 'safe'],
        ];
   }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function searchBusDetails()
    {
        $query = Ticket::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'db'=>Ticket::getDb(),
            'pagination' => false
        ]);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->where([
            'route' => $this->route,
            'dept_date' => $this->dept_date,
            'dept_time' => $this->dept_time,
            'start' => $this->start,
            'end' => $this->end,
        ]);
        
        $query->andWhere('status=\''.Ticket::STATUS_CONFIRMED.'\' OR status=\''.Ticket::STATUS_CARD_TICKET.'\'');
            
        $query->with('customerR');

        /*$dataProvider->db->cache(function() use ($dataProvider) {
            $dataProvider->prepare();
        });*/

        return $dataProvider;
    }
    
    public function search($params, $query = null)
    {
        if(empty($query))
            $query = Ticket::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'db'=>Ticket::getDb(),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        $query->andWhere("(status = 'CO' OR status = 'CT' )");

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'route' => $this->route,
            'dept_date' => $this->dept_date,
            'customer' => $this->customer,
            'issued_on' => $this->issued_on,
            'price' => $this->price,
            'discount' => $this->discount,
            'seat' => $this->seat,
            'is_deleted' => $this->is_deleted,
            'is_promo' => $this->is_promo,
            'is_staff' => $this->is_staff,
            'expired_in' => $this->expired_in,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'ticket', $this->ticket])
            ->andFilterWhere(['like', 'bus', $this->bus])
            ->andFilterWhere(['like', 'start', $this->start])
            ->andFilterWhere(['like', 'end', $this->end])
            ->andFilterWhere(['like', 'dept_time', $this->dept_time])
            ->andFilterWhere(['like', 'machine_serial', $this->machine_serial])
            ->andFilterWhere(['like', 'currency', $this->currency])
            ->andFilterWhere(['like', 'status', $this->status]);
            
        $query->with('customerR');

       /* $dataProvider->db->cache(function () use ($dataProvider) {
            $dataProvider->prepare();
        });*/

        return $dataProvider;
    }
    
    public function searchBooking($route, $date, $time)
    {
        $query = Ticket::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'db'=>Ticket::getDb(),
            'pagination' => [
                'pageSize' => 200,
            ],
        ]);

        // grid filtering conditions
        /*$query->andWhere(
            new \yii\db\Expression("DATE(FROM_UNIXTIME(created_at)) = '{$date}'")
        );*/
        
        $query->andWhere(['status'=>Ticket::STATUS_BOOKED]);
        
        $query->andFilterWhere([
            'route' => $route,
            'dept_time'=> $time,
        ]);
        $query->andWhere(
            new \yii\db\Expression("(UNIX_TIMESTAMP(CONCAT(dept_date, ' ', REPLACE(dept_time, 'H', ':'), ':00'))- expired_in)>UNIX_TIMESTAMP(CONVERT_TZ(NOW(), 'Africa/Kigali',  '".Yii::$app->user->identity->timezone."'))")
        );

        return $dataProvider;
    } 
    
    
    
    
    public function searchPromo($route, $start, $end)
    {
        $query = Ticket::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'db'=>Ticket::getDb(),
            'pagination' => [
                'pageSize' => 200,
            ],
        ]);

        // grid filtering conditions
        $query->andFilterWhere([
            'route' => $route,
            //'DATE(FROM_UNIXTIME(created_at))'=> $date,
        ])
        ->andWhere(['BETWEEN', 'dept_date', $start, $end])
        ->andWhere(['is_promo'=>1])
        ->andWhere(['status'=>Ticket::STATUS_CONFIRMED])
        ->orWhere(['status'=>Ticket::STATUS_CARD_TICKET]);

       /* $dataProvider->db->cache(function () use ($dataProvider) {
            $dataProvider->prepare();
        });*/
        
        return $dataProvider;
    }
	
	public function searchMobile($route, $start, $end)
    {
        $query = Ticket::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'db'=>Ticket::getDb(),
            'pagination' => [
                'pageSize' => 200,
            ],
        ]);

        // grid filtering conditions
        $query->andFilterWhere([
            'route' => $route,
            //'DATE(FROM_UNIXTIME(created_at))'=> $date,
        ])
        ->andWhere(['BETWEEN', 'date(from_unixTime(created_at))', $start, $end])
        ->andWhere(['created_by'=>110001])
        ->andWhere(['status'=>Ticket::STATUS_CONFIRMED])
        ->orWhere(['status'=>Ticket::STATUS_CARD_TICKET]);

       /* $dataProvider->db->cache(function () use ($dataProvider) {
            $dataProvider->prepare();
        });*/
       // var_dump($dataProvider);
		//die;
        return $dataProvider;
    }
    
    
    public function searchWeeklySales()
    {
      /*  $sql = 'SELECT DAYNAME(DATE(FROM_UNIXTIME(updated_at))) AS day, SUM(CASE WHEN currency= "RWF" THEN price-discount ELSE 0 END) AS RWF , SUM(CASE WHEN currency= "UGS" THEN price-discount ELSE 0 END) AS UGS, SUM(CASE WHEN currency= "FIB" THEN price-discount ELSE 0 END) AS FIB, SUM(CASE WHEN currency= "USD" THEN price-discount ELSE 0 END) AS USD FROM Tickets WHERE WEEK(FROM_UNIXTIME(updated_at)) = WEEK(CURDATE(), 1) AND (status=:tstatus OR cstatus) GROUP BY DAYNAME(DATE(FROM_UNIXTIME(updated_at)))';
        $query = Ticket::findBySql($sql, ['status'=>Ticket::STATUS_CONFIRMED, 'status'=>Ticket::STATUS_CARD_TICKET]); */
        


	 $sql = 'SELECT DAYNAME(DATE(FROM_UNIXTIME(updated_at))) AS day, SUM(CASE WHEN currency= "RWF" THEN price-discount ELSE 0 END) AS RWF , SUM(CASE WHEN currency= "UGS" THEN price-discount ELSE 0 END) AS UGS, SUM(CASE WHEN currency= "FIB" THEN price-discount ELSE 0 END) AS FIB, SUM(CASE WHEN currency= "USD" THEN price-discount ELSE 0 END) AS USD FROM Tickets WHERE WEEK(FROM_UNIXTIME(updated_at)) = WEEK(CURDATE(), 1) AND (status=:tstatus OR status=:cstatus) GROUP BY DAYNAME(DATE(FROM_UNIXTIME(updated_at)))';
        $query = Ticket::findBySql($sql, ['tstatus'=>Ticket::STATUS_CONFIRMED, 'cstatus'=>Ticket::STATUS_CARD_TICKET]);



        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'db'=>Ticket::getDb(),
            'pagination' =>false,
        ]);

       /* $dataProvider->db->cache(function () use ($dataProvider) {
            $dataProvider->prepare();
        });*/
        
        return $dataProvider;
    }
    
    public function searchUserSales($start, $end)
    {
        $sql = 'SELECT s.name AS author, s.location AS location, t.updated_by, COUNT(ticket) AS tickets, SUM(CASE WHEN t.currency= "RWF" THEN price-discount ELSE 0 END) AS RWF , SUM(CASE WHEN t.currency= "UGS" THEN price-discount ELSE 0 END) AS UGS, SUM(CASE WHEN t.currency= "FIB" THEN price-discount ELSE 0 END) AS FIB, SUM(CASE WHEN t.currency= "USD" THEN price-discount ELSE 0 END) AS USD FROM Tickets AS t INNER JOIN volcano_shared.Staffs AS s ON s.mobile = t.updated_by WHERE DATE(FROM_UNIXTIME(t.updated_at)) BETWEEN :start AND :end AND status=:status GROUP BY t.updated_by,s.name';
        $query = Ticket::findBySql($sql, ['status'=>Ticket::STATUS_CONFIRMED, 'start'=>$start, 'end'=>$end]);
        
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'db'=>Ticket::getDb(),
            'pagination' =>false,
        ]);

       /* $dataProvider->db->cache(function () use ($dataProvider) {
            $dataProvider->prepare();
        });*/
        
        return $dataProvider;
    }
    
    public function searchPosSales($start, $end)
    {
        $sql = 'SELECT machine_serial, COUNT(ticket) AS tickets, SUM(CASE WHEN currency= "RWF" THEN price-discount ELSE 0 END) AS RWF , SUM(CASE WHEN currency= "UGS" THEN price-discount ELSE 0 END) AS UGS, SUM(CASE WHEN currency= "FIB" THEN price-discount ELSE 0 END) AS FIB, SUM(CASE WHEN currency= "USD" THEN price-discount ELSE 0 END) AS USD FROM Tickets WHERE DATE(FROM_UNIXTIME(updated_at)) BETWEEN :start AND :end AND (status=:statusTickets) GROUP BY machine_serial';
        $query = Ticket::findBySql($sql, ['statusTickets'=>Ticket::STATUS_CONFIRMED,/*'statusCards'=>Ticket::STATUS_CARD_TICKET,*/ 'start'=>$start, 'end'=>$end]);
        
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'db'=>Ticket::getDb(),
            'pagination' =>false,
        ]);

       /* $dataProvider->db->cache(function () use ($dataProvider) {
            $dataProvider->prepare();
        });*/
        
        return $dataProvider;
    }
    
    public function searchBestSellers()
    {
        $sql = 'SELECT CONCAT(name, " \n ", mobile) AS author, SUM(CASE WHEN currency= "RWF" THEN price-discount  WHEN currency= "FIB" THEN (price-discount)*2.05 ELSE (price-discount)*4.32 END) AS RWF FROM Tickets INNER JOIN volcano_shared.Staffs s ON s.mobile = Tickets.updated_by WHERE MONTH(FROM_UNIXTIME(Tickets.updated_at)) = MONTH(CURDATE()) AND YEAR(FROM_UNIXTIME(Tickets.updated_at)) = YEAR(CURDATE()) AND status=:status GROUP BY name, mobile, Tickets.updated_by LIMIT 5';//TDL the exchange rate of 4.32 should be in config
        $query = Ticket::findBySql($sql, ['status'=>Ticket::STATUS_CONFIRMED]);
        
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'db'=>Ticket::getDb(),
            'pagination' =>false,
        ]);

       /* $dataProvider->db->cache(function () use ($dataProvider) {
            $dataProvider->prepare();
        });*/
        
        return $dataProvider;
    }
    
    
    public function searchBestRoutes()
    {
        $sql = 'SELECT CONCAT(Routes.start, " - ", Routes.end) AS broute, SUM(CASE WHEN currency= "RWF" THEN price-discount WHEN currency = "FIB" THEN (price-discount)*2.05  ELSE (price-discount)*4.32 END) AS RWF FROM Tickets INNER JOIN Routes ON Routes.id = Tickets.route WHERE MONTH(FROM_UNIXTIME(Tickets.updated_at)) = MONTH(CURDATE()) AND YEAR(FROM_UNIXTIME(Tickets.updated_at)) = YEAR(CURDATE()) AND status=:status GROUP BY Routes.start, Routes.end, Tickets.route LIMIT 5';//TDL the exchange rate of 4.32 should be in config
        $query = Ticket::findBySql($sql, ['status'=>Ticket::STATUS_CONFIRMED]);
        
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'db'=>Ticket::getDb(),
            'pagination' =>false,
        ]);

       /* $dataProvider->db->cache(function () use ($dataProvider) {
            $dataProvider->prepare();
        });*/
        
        return $dataProvider;
    }
    
    
    public function searchRoutes($start, $end)
    {
        $where = '';
        $whereParams = [
            'statusTickets'=>Ticket::STATUS_CONFIRMED,
            'statusCards'=>Ticket::STATUS_CARD_TICKET,
            'booked'=>Ticket::STATUS_BOOKED, 
            'exp'=>time(),
            ':start'=>$start,
            ':end'=>$end,
        ];
        
        if (!empty($this->dept_date)) {
            $where = $where.' AND dept_date = :dept_date';
            $whereParams['dept_date'] = $this->dept_date;
        }
        if (!empty($this->dept_time)) {
            $where = $where.' AND dept_time = :dept_time';
            $whereParams['dept_time'] = $this->dept_time;
        }
        if (!empty($this->bus)) {
            $where = $where.' AND bus = :bus';
            $whereParams['bus'] = $this->bus;
        }
        if (!empty($this->route)) {
            $where = $where.' AND route = :route';
            $whereParams['route'] = $this->route;
        }
        
        $sql = 'SELECT r.id as route, r.start, r.end, SUM(CASE WHEN currency= "RWF" AND (status=:statusTickets OR status=:statusCards) THEN price ELSE 0 END) AS RWF , SUM(CASE WHEN currency= "UGS" AND (status=:statusTickets OR status=:statusCards) THEN price-discount ELSE 0 END) AS UGS, SUM(CASE WHEN currency= "FIB" AND (status=:statusTickets OR status=:statusCards) THEN price-discount ELSE 0 END) AS FIB, SUM(CASE WHEN currency= "USD" AND (status=:statusTickets OR status=:statusCards) THEN price-discount ELSE 0 END) AS USD, bus, dept_date, dept_time, total_seats AS seats, SUM(CASE WHEN (status=:statusTickets OR status=:statusCards) THEN 1 ELSE 0 END) AS tickets, SUM(CASE WHEN status=:booked THEN 1 ELSE 0 END) AS bookings,  SUM(CASE WHEN is_staff=1 AND (status=:statusTickets OR status=:statusCards) THEN 1 ELSE 0 END) AS staff, SUM(CASE WHEN is_promo=1 AND (status=:statusTickets OR status=:statusCards) THEN 1 ELSE 0 END) AS promotion FROM Tickets t INNER JOIN Buses b ON t.bus = b.regno INNER JOIN Routes r ON t.route = r.id WHERE ((t.status=:statusTickets OR t.status=:statusCards) OR (status=:booked AND expired_in+issued_on>:exp)) AND dept_date BETWEEN :start AND :end '.$where.' GROUP BY route, bus, dept_date, dept_time, r.start, r.end ORDER BY dept_date, dept_time';
        $query = Ticket::findBySql($sql, $whereParams);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'db'=>Ticket::getDb(),
        ]);

       /* $dataProvider->db->cache(function () use ($dataProvider) {
            $dataProvider->prepare();
        });*/

        return $dataProvider;
    }
    
    public function searchRouteDetails($dept_date, $dept_time, $route, $filter_date)
    {
        $sql = 'SELECT route,t.start, t.end, SUM(CASE WHEN currency= "RWF" THEN price-discount ELSE 0 END) AS RWF , SUM(CASE WHEN currency= "UGS" THEN price-discount ELSE 0 END) AS UGS,SUM(CASE WHEN currency= "FIB" THEN price-discount ELSE 0 END) AS FIB,SUM(CASE WHEN currency= "USD" THEN price-discount ELSE 0 END) AS USD, bus, dept_date, dept_time, total_seats AS seats, COUNT(ticket) AS tickets FROM Tickets t INNER JOIN Buses b ON t.bus = b.regno INNER JOIN Routes r ON t.route = r.id WHERE (t.status=:statusTickets OR t.status=:statusCards)  AND DATE(FROM_UNIXTIME(t.updated_at)) = :date AND route=:route AND dept_date = :dept_date AND dept_time = :dept_time GROUP BY route, bus, dept_date, dept_time, t.start, t.end ORDER BY tickets DESC, t.end';
        $query = Ticket::findBySql($sql, [
            'statusTickets'=>Ticket::STATUS_CONFIRMED,
            'statusCards'=>Ticket::STATUS_CARD_TICKET,
            'date'=>$filter_date, 
            'route'=>$route,
            'dept_date'=>$dept_date,
            'dept_time'=>$dept_time,
        ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'db'=>Ticket::getDb(),
        ]);

       /* $dataProvider->db->cache(function () use ($dataProvider) {
            $dataProvider->prepare();
        });*/

        return $dataProvider;
    }
}
