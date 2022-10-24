<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Customers".
 *
 * @property integer $mobile
 * @property string $name
 * @property string $id
 * @property integer $created_at
 * @property integer $created_by
 * @property integer $updated_at
 * @property integer $updated_by
 * @property string $nationality
 * @property integer $gender
 * @property integer $dob
 *
 * @property Staffs $createdBy
 * @property Staffs $updatedBy
 * @property Points[] $points
 * @property Routes[] $routes
 * @property Tickets[] $tickets
 */
class Customer extends TenantModel
{
    use \app\traits\Signature;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Customers';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['mobile', 'name'], 'required'],
            [['created_at', 'created_by', 'updated_at', 'updated_by', 'gender'], 'integer'],
            [['name'], 'string', 'max' => 100],
            [['mobile'], 'string', 'max' => 256],
            [['dob'], 'date', 'format' => 'php:Y-m-d'],
            [['id', 'nationality'], 'string', 'max' => 45],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => Staff::className(), 'targetAttribute' => ['created_by' => 'mobile']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => Staff::className(), 'targetAttribute' => ['updated_by' => 'mobile']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'mobile' => Yii::t('app', 'Mobile'),
            'name' => Yii::t('app', 'Name'),
            'id' => Yii::t('app', 'ID'),
            'created_at' => Yii::t('app', 'Created At'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'nationality' => Yii::t('app', 'Nationality'),
            'gender' => Yii::t('app', 'Gender'),
            'dob' => Yii::t('app', 'dob'),
            'from_nation' => Yii::t('app', 'From Nation'),
            'to_nation' => Yii::t('app', 'To Nation'),
        ];
    }

    public function getGenderName()
    {
        return $this->gender == 1 ? 'Male' : 'Female';
    }

    public function getGenderShortName()
    {
        return $this->gender == 1 ? 'M' : 'F';
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
    public function getPoints()
    {
        return $this->hasMany(Point::className(), ['customer' => 'mobile']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRoutes()
    {
        return $this->hasMany(Route::className(), ['id' => 'route'])->viaTable('Points', ['customer' => 'mobile']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTickets()
    {
        return $this->hasMany(Ticket::className(), ['customer' => 'mobile']);
    }
}
