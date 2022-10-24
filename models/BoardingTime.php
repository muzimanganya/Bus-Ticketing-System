<?php

namespace app\models;

use Yii;
use app\traits\Signature;

/**
 * This is the model class for table "BoardingTimes".
 *
 * @property integer $route
 * @property string $start
 * @property string $end
 * @property integer $offset
 * @property integer $created_at
 * @property integer $created_by
 * @property integer $updated_at
 * @property integer $updated_by
 *
 * @property Stops $end0
 * @property Routes $route0
 * @property Stops $start0
 */
class BoardingTime extends TenantModel
{
    use Signature;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'BoardingTimes';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['route', 'start', 'end', 'offset'], 'required'],
            [['route', 'offset', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['start', 'end'], 'string', 'max' => 100],
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
            'route' => Yii::t('app', 'Route'),
            'start' => Yii::t('app', 'Coming From'),
            'end' => Yii::t('app', 'Going To'),
            'offset' => Yii::t('app', 'Offset (Minutes)'),
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
        return $this->hasOne(Stops::className(), ['name' => 'start']);
    }
}
