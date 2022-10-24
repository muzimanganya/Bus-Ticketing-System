<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "CardLogs".
 *
 * @property integer $id
 * @property integer $card
 * @property integer $created_at
 * @property integer $created_by
 * @property integer $updated_at
 * @property integer $updated_by
 * @property integer $remained_trips
 *
 * @property RouteCards $card0
 */
class CardLog extends TenantModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'CardLogs';
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['card', 'created_at', 'created_by', 'updated_at', 'updated_by', 'remained_trips', 'id'], 'required'],
            [['card', 'created_at', 'created_by', 'updated_at', 'updated_by', 'remained_trips', 'id'], 'integer'],
            [['card'], 'exist', 'skipOnError' => true, 'targetClass' => RouteCard::className(), 'targetAttribute' => ['card' => 'card']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'card' => Yii::t('app', 'Card'),
            'created_at' => Yii::t('app', 'Sold'),
            'created_by' => Yii::t('app', 'Cashier'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'remained_trips' => Yii::t('app', 'Remained Trips'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCardR()
    {
        return $this->hasOne(RouteCard::className(), ['card' => 'card']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(Staff::className(), ['mobile' => 'created_by']);
    }
}
