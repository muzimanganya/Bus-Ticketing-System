<?php

namespace app\models;

use Yii;
use app\models\TenantModel;

/**
 * This is the model class for table "Points".
 *
 * @property string $customer
 * @property string $start
 * @property string $end
 * @property integer $points
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Customers $customer0
 * @property Stops $end0
 * @property Stops $start0
 */
class Point extends TenantModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Points';
    }
 

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['customer', 'start', 'end'], 'required'],
            [['points', 'created_at', 'updated_at'], 'integer'],
            [['customer'], 'string', 'max' => 255],
            [['start', 'end'], 'string', 'max' => 100],
            [['customer'], 'exist', 'skipOnError' => true, 'targetClass' => Customer::className(), 'targetAttribute' => ['customer' => 'mobile']],
            [['end'], 'exist', 'skipOnError' => true, 'targetClass' => Stop::className(), 'targetAttribute' => ['end' => 'name']],
            [['start'], 'exist', 'skipOnError' => true, 'targetClass' => Stop::className(), 'targetAttribute' => ['start' => 'name']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'customer' => Yii::t('app', 'Customer'),
            'start' => Yii::t('app', 'Start'),
            'end' => Yii::t('app', 'End'),
            'points' => Yii::t('app', 'Points'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCustomer0()
    {
        return $this->hasOne(Customers::className(), ['mobile' => 'customer']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEnd0()
    {
        return $this->hasOne(Stops::className(), ['name' => 'end']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStart0()
    {
        return $this->hasOne(Stops::className(), ['name' => 'start']);
    }
}
