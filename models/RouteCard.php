<?php

namespace app\models;

use Yii;
use app\traits\Signature;

/**
 * This is the model class for table "RouteCards".
 *
 * @property integer $card
 * @property string $start
 * @property string $end
 * @property integer $price
 * @property string $currency
 * @property integer $remaining_trips
 * @property integer $total_trips
 * @property string $owner
 * @property string $is_sold
 * @property integer $created_at
 * @property integer $created_by
 * @property integer $updated_at
 * @property integer $updated_by
 *
 * @property Customers $owner0
 */
class RouteCard extends TenantModel
{
    use Signature;
    
    public $multicard; //for inputting multiple card ID of the same route
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'RouteCards';
    }

    public function init()
    {
        if ($this->isNewRecord) {
            $this->currency = 'RWF';
            $this->total_trips = 10;
        }
        return parent::init();
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['card','start', 'end', 'price', 'total_trips', 'currency'], 'required'],
            [['card','is_sold','sold_by', 'remaining_trips', 'price', 'total_trips', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['start', 'end', 'pos'], 'string', 'max' => 100],
            [['currency'], 'string', 'max' => 4],
            ['currency', 'default', 'value' => 'RWF'],
            [['owner'], 'string', 'max' => 255],
            [['multicard'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'card' => Yii::t('app', 'Card Number'),
            'pos' => Yii::t('app', 'Transacting POS'),
            'route' => Yii::t('app', 'Main Route'),
            'start' => Yii::t('app', 'Coming From'),
            'end' => Yii::t('app', 'Going To'),
            'price' => Yii::t('app', 'Trip Price'),
            'currency' => Yii::t('app', 'Currency'),
            'total_trips' => Yii::t('app', 'Total Trips'),
            'owner' => Yii::t('app', 'Card Owner'),
            'created_at' => Yii::t('app', 'Created At'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'updated_by' => Yii::t('app', 'Updated By'),
        ];
    }
    
    public function beforeSave($insert)
    {
        $return = parent::beforeSave($insert);
        
        if ($this->isNewRecord) {
            $this->remaining_trips = $this->total_trips;
        }
        return $return;
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
    public function getSoldBy()
    {
        return $this->hasOne(Staff::className(), ['mobile' => 'sold_by']);
    }
}
