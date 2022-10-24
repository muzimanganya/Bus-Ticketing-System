<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Pricing".
 *
 * @property string $start
 * @property string $end
 * @property integer $route
 * @property integer $price
 * @property string $currency
 * @property integer $created_at
 * @property integer $created_by
 * @property integer $updated_at
 * @property integer $updated_by
 *
 * @property Stops $end0
 * @property Routes $route0
 * @property Stops $start0
 */
class Pricing extends TenantModel
{
    use \app\traits\Signature;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Pricing';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['start', 'end', 'route', 'price', 'currency'], 'required'],
            [['route', 'price', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['start', 'end'], 'string', 'max' => 100],
            [['currency'], 'string', 'max' => 5],
            [['end'], 'exist', 'skipOnError' => true, 'targetClass' => Stop::className(), 'targetAttribute' => ['end' => 'name']],
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
            'start' => Yii::t('app', 'From'),
            'end' => Yii::t('app', 'To'),
            'route' => Yii::t('app', 'Route'),
            'price' => Yii::t('app', 'Price'),
            'currency' => Yii::t('app', 'Currency'),
            'created_at' => Yii::t('app', 'Created At'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'updated_by' => Yii::t('app', 'Updated By'),
        ];
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
    public function getRouteR()
    {
        return $this->hasOne(Route::className(), ['id' => 'route']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStart0()
    {
        return $this->hasOne(Stop::className(), ['name' => 'start']);
    }
}
