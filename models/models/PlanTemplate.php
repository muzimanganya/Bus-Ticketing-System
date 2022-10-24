<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "PlanTemplates".
 *
 * @property integer $route
 * @property string $bus
 * @property string $hour
 *
 * @property Buses $bus0
 * @property Routes $route0
 */
class PlanTemplate extends TenantModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'PlanTemplates';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['route', 'bus', 'hour', 'pcapacity'], 'required'],
            [['route', 'pcapacity'], 'integer'],
            [['bus'], 'string', 'max' => 20],
            [['hour'], 'string', 'max' => 5],
            [['bus'], 'exist', 'skipOnError' => true, 'targetClass' => Bus::className(), 'targetAttribute' => ['bus' => 'regno']],
            [['route'], 'exist', 'skipOnError' => true, 'targetClass' => Route::className(), 'targetAttribute' => ['route' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'route' => Yii::t('app', 'Route'),
            'bus' => Yii::t('app', 'Bus'),
            'hour' => Yii::t('app', 'Hour'),
            'pcapacity' => Yii::t('app', 'Route Capacity'),
        ];
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
    public function getRouteR()
    {
        return $this->hasOne(Route::className(), ['id' => 'route']);
    }
}
