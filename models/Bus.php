<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Buses".
 *
 * @property string $regno
 * @property integer $leftseats
 * @property integer $rightseats
 * @property string $backseats
 * @property string $doorside
 * @property integer $driver
 * @property integer $total_seats
 * @property integer $created_at
 * @property integer $created_by
 * @property integer $updated_at
 * @property integer $updated_by
 *
 * @property Staffs $createdBy
 * @property Staffs $driver0
 * @property Staffs $updatedBy
 * @property Routes[] $routes
 * @property Tickets[] $tickets
 */
class Bus extends TenantModel
{
    use \app\traits\Signature;

    public $sides = ['l' => 'Left', 'r' => 'Right'];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Buses';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['regno', 'leftseats', 'rightseats', 'backseats', 'driver', 'total_seats'], 'required'],
            [['leftseats', 'rightseats', 'driver', 'total_seats', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['regno'], 'string', 'max' => 20],
            [['backseats'], 'string', 'max' => 45],
            [['doorside'], 'string', 'max' => 1],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => Staff::className(), 'targetAttribute' => ['created_by' => 'mobile']],
            [['driver'], 'exist', 'skipOnError' => true, 'targetClass' => Staff::className(), 'targetAttribute' => ['driver' => 'mobile']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => Staff::className(), 'targetAttribute' => ['updated_by' => 'mobile']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'regno' => Yii::t('app', 'Registration Number'),
            'leftseats' => Yii::t('app', 'Left Seats'),
            'rightseats' => Yii::t('app', 'Right Seats'),
            'backseats' => Yii::t('app', 'Back Seats'),
            'doorside' => Yii::t('app', 'Door Side'),
            'driver' => Yii::t('app', 'Driver'),
            'total_seats' => Yii::t('app', 'Total Seats'),
            'created_at' => Yii::t('app', 'Added On'),
            'created_by' => Yii::t('app', 'Added By'),
            'updated_at' => Yii::t('app', 'Updated On'),
            'updated_by' => Yii::t('app', 'Updated By'),
        ];
    }


    public function getDoorSide()
    {
        return $this->sides[$this->doorside];
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
    public function getDriverR()
    {
        return $this->hasOne(Staff::className(), ['mobile' => 'driver']);
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
    public function getRoutes()
    {
        return $this->hasMany(Route::className(), ['bus' => 'regno']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTickets()
    {
        return $this->hasMany(Ticket::className(), ['bus' => 'regno']);
    }
}
