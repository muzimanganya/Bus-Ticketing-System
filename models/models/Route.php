<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Routes".
 *
 * @property integer $id
 * @property string $start
 * @property string $end
 * @property string $bus
 * @property integer $parent
 * @property integer $idx
 * @property integer $send_sms
 * @property integer $created_at
 * @property integer $created_by
 * @property integer $updated_at
 * @property integer $updated_by
 * @property integer $has_promotion
 * @property integer $is_intl
 * @property string $customer_care
 *
 * @property PlannedRoutes[] $plannedRoutes
 * @property Points[] $points
 * @property Customers[] $customers
 * @property ReservedSeats[] $reservedSeats
 * @property Stops $end0
 * @property Staffs $createdBy
 * @property Staffs $updatedBy
 * @property Routes $parent0
 * @property Routes[] $routes
 * @property Stops $start0
 * @property SMS[] $sMSs
 * @property Tickets[] $tickets
 */
class Route extends TenantModel
{
    use \app\traits\Signature;

    public $sendSMS = [0=>'Don\'t Send SMS', 1=>'Send SMS'];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Routes';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['start', 'end', 'idx', 'is_intl','is_return', 'has_promotion', 'customer_care'], 'required'],
            [['parent','return','is_return', 'idx', 'send_sms', 'created_at', 'created_by', 'updated_at', 'updated_by', 'has_promotion', 'is_intl'], 'integer'],
            [['start', 'end'], 'string', 'max' => 100],
            [['customer_care', 'name'], 'string', 'max' => 45],
            [['end'], 'exist', 'skipOnError' => true, 'targetClass' => Stop::className(), 'targetAttribute' => ['end' => 'name']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => Staff::className(), 'targetAttribute' => ['created_by' => 'mobile']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => Staff::className(), 'targetAttribute' => ['updated_by' => 'mobile']],
            [['parent'], 'exist', 'skipOnError' => true, 'targetClass' => Parent::className(), 'targetAttribute' => ['parent' => 'id']],
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
            'start' => Yii::t('app', 'First Stop'),
            'end' => Yii::t('app', 'End Stop'),
            'cost' => Yii::t('app', 'Cost'),
            'parent' => Yii::t('app', 'Parent'),
            'idx' => Yii::t('app', 'Route Index'),
            'send_sms' => Yii::t('app', 'Send Sms'),
            'created_at' => Yii::t('app', 'Created At'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'has_promotion' => Yii::t('app', 'Promotion Enabled'),
            'is_intl' => Yii::t('app', 'Route Type'),
            'customer_care' => Yii::t('app', 'Customer Care'),
        ];
    }

    /**
     * check if has the said subroute
     */
    public function hasRoute($start, $end)
    {
        //if route is parent then return true immediately
        if ($this->start== $start && $this->end == $end) {
            return true;
        }
            
        $sql = 'SELECT COUNT(*) FROM Routes  WHERE parent = :parent AND  idx BETWEEN (SELECT idx FROM Routes  WHERE parent = :parent AND start=:start ORDER BY idx ASC LIMIT 1) AND (SELECT idx FROM Routes  WHERE parent = :parent AND end=:end ORDER BY idx ASC LIMIT 1)';
        $routes =  Route::getDb()->createCommand($sql)
                        ->bindValue(':start', $start)
                        ->bindValue(':end', $end)
                        ->bindValue(':parent', $this->id)
                        ->queryScalar();
        return $routes>0 ;
    }
    
    public function getIsInternational()
    {
        return $this->is_intl;
    }
    
    public function getRouteName()
    {
        return $this->name.' ('.$this->start.' - '.$this->end.')';
    }
    
    
    public function getStartEnd()
    {
        return $this->start.'-'.$this->end;
    }
    
    
    public function extraFields() {
        return ['children'];
    }
    
    /**
    * @return \yii\db\ActiveQuery
    */
    public function getReturnR()
    {
        return $this->hasOne(Route::className(), ['id' => 'return']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPoints()
    {
        return $this->hasMany(Point::className(), ['route' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCustomers()
    {
        return $this->hasMany(Customer::className(), ['mobile' => 'customer'])->viaTable('Points', ['route' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEnd0()
    {
        return $this->hasOne(Stop::className(), ['name' => 'end']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(Staff::className(), ['mobile' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(Staff::className(), ['mobile' => 'updated_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParentR()
    {
        return $this->hasOne(Route::className(), ['id' => 'parent']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChildren()
    {
        return $this->hasMany(Route::className(), ['parent' => 'id'])
                    ->select(['id', 'start', 'end', 'parent']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStart0()
    {
        return $this->hasOne(Stop::className(), ['name' => 'start']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSMSs()
    {
        return $this->hasMany(SMS::className(), ['route' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTickets()
    {
        return $this->hasMany(Ticket::className(), ['route' => 'id']);
    }
}
