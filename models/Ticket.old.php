<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Tickets".
 *
 * @property integer $id
 * @property string $ticket
 * @property integer $route
 * @property string $bus
 * @property string $start
 * @property string $end
 * @property string $dept_date
 * @property string $dept_time
 * @property integer $customer
 * @property integer $issued_on
 * @property string $machine_serial
 * @property integer $price
 * @property integer $is_deleted
 * @property string $status
 * @property integer $expired_in
 * @property integer $created_at
 * @property integer $created_by
 * @property integer $updated_at
 * @property integer $updated_by
 *
 * @property PlannedRoutes $deptDate
 * @property Buses $bus0
 * @property Customers $customer0
 * @property Stops $end0
 * @property POSes $machineSerial
 * @property Routes $route0
 * @property Stops $start0
 */
class Ticket extends TenantModel
{
    use \app\traits\Signature;

    const STATUS_BOOKED = 'BO';
    const STATUS_TEMPORARY_BOOKED = 'BT';
    const STATUS_CANCELLED = 'CA';
    const STATUS_CONFIRMED = 'CO';
    const STATUS_CARD_TICKET = 'CT';
    const STATUS_FREE = 'FR';
    
    public $RWF;
    public $UGS;
    public $FIB;
    public $USD;
    public $seats; // total bus seats
    public $tickets; // sold tickets
    public $bookings; // valid bookings
    public $promotion; // Promotion tickets
    public $staff; // Staff tickets
    public $location; // Staff Location
    public $day; // Ticket day like Monday
    public $author; // Staff who created ticket
    public $broute; // Main Route
    
    public $cards; //total cards sold
    public $crevenue; //total cards Revenue
    /**
     * @inheritdoc
     */
    public static function tableName() 
    {
        return 'Tickets';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ticket', 'route', 'bus', 'start', 'end', 'dept_date', 'dept_time', 'customer', 'issued_on', 'machine_serial', 'price', 'expired_in'], 'required'],
            [['route', 'seat', 'issued_on', 'price', 'is_deleted','is_promo', 'is_staff', 'expired_in', 'created_at', 'created_by', 'updated_at', 'updated_by', 'is_printed'], 'integer'],
	    [['dept_date'], 'safe'],
            [['ticket', 'start', 'end', 'machine_serial', 'mobile_money'], 'string', 'max' => 100],
            [['bus', 'day'], 'string', 'max' => 20],
            [['dept_time'], 'string', 'max' => 45],
            [['customer'], 'string', 'max' => 256],
            [['status'], 'string', 'max' => 2],
            [['dept_date'], 'exist', 'skipOnError' => true, 'targetClass' => PlannedRoute::className(), 'targetAttribute' => ['dept_date' => 'dept_date']],
            [['bus'], 'exist', 'skipOnError' => true, 'targetClass' => Bus::className(), 'targetAttribute' => ['bus' => 'regno']],
            [['customer'], 'exist', 'skipOnError' => true, 'targetClass' => Customer::className(), 'targetAttribute' => ['customer' => 'mobile']],
            [['end'], 'exist', 'skipOnError' => true, 'targetClass' => Stop::className(), 'targetAttribute' => ['end' => 'name']],
            [['machine_serial'], 'exist', 'skipOnError' => true, 'targetClass' => POS::className(), 'targetAttribute' => ['machine_serial' => 'serial']],
            [['route'], 'exist', 'skipOnError' => true, 'targetClass' => Route::className(), 'targetAttribute' => ['route' => 'id']],
            [['start'], 'exist', 'skipOnError' => true, 'targetClass' => Stop::className(), 'targetAttribute' => ['start' => 'name']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'ticket' => Yii::t('app', 'Ticket'),
            'route' => Yii::t('app', 'Route'),
            'bus' => Yii::t('app', 'Bus'),
            'start' => Yii::t('app', 'Departure'),
            'end' => Yii::t('app', 'Destination'),
            'dept_date' => Yii::t('app', 'Departure Date'),
            'dept_time' => Yii::t('app', 'Time'),
            'customer' => Yii::t('app', 'Customer'),
            'issued_on' => Yii::t('app', 'Issued On'),
            'machine_serial' => Yii::t('app', 'Machine Serial'),
            'price' => Yii::t('app', 'Price'),
            'is_deleted' => Yii::t('app', 'Is Deleted'),
            'status' => Yii::t('app', 'Status'),
            'expired_in' => Yii::t('app', 'Expired In'),
            'created_at' => Yii::t('app', 'Created At'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'currency' => Yii::t('app', 'Currency'),
            'paymentCurrency' => Yii::t('app', 'Currency'),
            'bus_route' => Yii::t('app', 'Bus Route'),
            'RWF' => Yii::t('app', 'RWF'),
            'FIB' => Yii::t('app', 'FIB'),
            'UGS' => Yii::t('app', 'UGS'),
            'USD' => Yii::t('app', 'USD'),
            'seats' => Yii::t('app', 'Total Seats'),
            'tickets' => Yii::t('app', 'Sold Tickets'),
        ];
    }
    
    public function getStartEnd()
    {
        return $this->start.'-'.$this->end;
    }
    
    public function getBusRoute()
    {
        return $this->start.' - '.$this->end;
    }
    
    public function getPaymentCurrency()
    {
        return $this->currencies[$this->currency];
    }

    public function isSeatOccupied($seat)
    {
        $count = Ticket::find()
                ->where(['route'=>$this->route,
                    'dept_date'=>$this->dept_date,
                    'dept_time'=>$this->dept_time,
                    'bus'=>$this->bus,
                    'seat'=>$seat])
                ->count();
        return $count>0;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDeptDate()
    {
        return $this->hasOne(PlannedRoutes::className(), ['dept_date' => 'dept_date']);
    }

    public function getUpdatedBy()
    {

	return $this->hasOne(Staff::className(), ['mobile'=>'updated_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBusR()
    {
        return $this->hasOne(Bus::className(), ['regno' => 'bus']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCustomerR()
    {
        return $this->hasOne(Customer::className(), ['mobile' => 'customer']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMachineSerial()
    {
        return $this->hasOne(POS::className(), ['serial' => 'machine_serial']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRouteR()
    {
        return $this->hasOne(Route::className(), ['id' => 'route']);
    }


    public function getProute()
    {
        return PlannedRoute::findOne([
            'route' => $this->route,
            'dept_time'=>$this->dept_time,
            'dept_date'=>$this->dept_date,
            'bus'=>$this->bus
        ]);
    }
    
    public function getUserCardSoldAmount($date)
    {
        return PlannedRoute::findOne([
            'route' => $this->route,
            'dept_time'=>$this->dept_time,
            'dept_date'=>$this->dept_date,
            'bus'=>$this->bus
        ]);
    }
}
