<?php

namespace app\models;

use Yii;
use yii\data\SqlDataProvider;

/**
 * This is the model class for table "PlannedRoute".
 *
 * @property integer $route
 * @property string $dept_date
 * @property string $dept_time
 * @property string $bus
 * @property integer $created_at
 * @property integer $created_by
 * @property integer $updated_at
 * @property integer $updated_by
 *
 * @property Staff $createdBy
 * @property Bus $bus0
 * @property Staff $updatedBy
 * @property Route $route0
 * @property SMS[] $sMSs
 * @property SMS[] $sMSs0
 * @property Tickets[] $tickets
 */
class PlannedRoute extends TenantModel
{
    use \app\traits\Signature;
 
    public $comment;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'PlannedRoutes';
    } 

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['route', 'dept_date', 'dept_time', 'bus'], 'required'],
            [['route', 'capacity', 'created_at', 'created_by', 'updated_at', 'updated_by', 'priority'], 'integer'],
            [['dept_date'], 'safe'],
            [['dept_time'], 'string', 'max' => 5],
            [['comment'], 'string', 'max' => 500],
            [['bus'], 'string', 'max' => 20],
            [['dept_date'], 'planExists'],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => Staff::className(), 'targetAttribute' => ['created_by' => 'mobile']],
            [['bus'], 'exist', 'skipOnError' => true, 'targetClass' => Bus::className(), 'targetAttribute' => ['bus' => 'regno']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => Staff::className(), 'targetAttribute' => ['updated_by' => 'mobile']],
            [['route'], 'exist', 'skipOnError' => true, 'targetClass' => Route::className(), 'targetAttribute' => ['route' => 'id']],
        ];
    }
    
    
    public function fields()
    {
        $fields = parent::fields();
        $fields['bus'] = function ($model) {
            $bus = $model->busR->toArray();
            unset($bus['created_at']);
            unset($bus['created_by']);
            unset($bus['driver']);
            unset($bus['updated_at']);
            unset($bus['updated_by']);
            return $bus;
        };

        $fields['routeName'] = function ($model) {
            return "{$model->routeR->start} - {$model->routeR->end}";
        };

        return $fields;
    }
    
    public function planExists($attribute, $params)
    {
        $exists = PlannedRoute::find()->where([
                'bus' => $this->bus,
                'route' => $this->route,
                'dept_date' => $this->dept_date,
                'dept_time' => $this->dept_time])->exists();
                
        $error =  'The Route is already Planned';
        //var_dump($this->attributes); die();
        if ($exists) {
            $attr = $this->oldAttributes;
            //var_dump($attr); die();
            if (empty($attr)) { //new record
                $this->addError($attribute, $error);
            } elseif ($this->route!=$attr['route'] || $this->dept_date!=$attr['dept_date'] || $this->dept_time!=$attr['dept_time']) {
                $this->addError($attribute, $error);
            }
        }
    }
    
    public function getTravellers()
    {
        $sql = 'SELECT t.ticket, t.bus, t.seat, c.mobile, c.name, c.passport,t.start,t.end, (CASE WHEN c.gender=1 THEN "MALE" ELSE "FEMALE" END) AS gender, c.age, c.nationality, from_nation, c.to_nation  FROM Tickets t INNER JOIN Customers c ON c.mobile = t.customer WHERE t.status="CO" and t.dept_date = :date AND t.dept_time=:time AND t.route = :route AND t.bus=:bus';
        
        $sqlCount = 'SELECT COUNT(*) FROM Tickets t WHERE t.dept_date = :date AND t.dept_time=:time AND t.route = :route AND t.bus=:bus';
        
        $count = PlannedRoute::getDb()->createCommand($sqlCount, [
            ':date'=>$this->dept_date,
            ':time'=>$this->dept_time,
            ':route'=>$this->route,
            ':bus'=>$this->bus,
        ])->queryScalar();
        
        $dataProvider =  new SqlDataProvider([
            'sql' => $sql,
            'db' => PlannedRoute::getDb(),
            'params' => [
                ':date'=>$this->dept_date,
                ':time'=>$this->dept_time,
                ':route'=>$this->route,
                ':bus'=>$this->bus,
            ],
            'totalCount' => $count,
            'pagination' => [
                'pageSize' => 50,
            ],
            'sort' => [
                'attributes' => [
                    'name',
                    'seat',
                    'mobile',
                    'passport',
                    'gender',
                    'nationality',
		    'end',
                ],
            ],
        ]);
        return $dataProvider;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'dept_date' => Yii::t('app', 'Departure Date'),
            'dept_time' => Yii::t('app', 'Departure Time'),
            'route' => Yii::t('app', 'Main Route'),
            'bus' => Yii::t('app', 'Bus'),
            'capacity' => Yii::t('app', 'Bus Cpacity'),
            'created_at' => Yii::t('app', 'Created At'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'updated_by' => Yii::t('app', 'Updated By'),
        ];
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
    public function getBusR()
    {
        return $this->hasOne(Bus::className(), ['regno' => 'bus']);
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
    public function getRouteR()
    {
        return $this->hasOne(Route::className(), ['id' => 'route']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSMSs()
    {
        return $this->hasMany(SMS::className(), ['dept_date' => 'dept_date']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSMSs0()
    {
        return $this->hasMany(SMS::className(), ['dept_time' => 'dept_time']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTickets()
    {
        return $this->hasMany(Tickets::className(), ['dept_date' => 'dept_date']);
    }
}
