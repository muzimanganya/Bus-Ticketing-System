<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "POSes".
 *
 * @property string $serial
 * @property integer $mobile
 * @property string $simcard
 * @property integer $location
 * @property integer $created_at
 * @property integer $created_by
 * @property integer $updated_at
 * @property integer $updated_by
 * @property integer $is_active
 *
 * @property Staff $createdBy
 * @property Staff $updatedBy
 * @property Staff $location0
 * @property Tickets[] $tickets
 */
class POS extends TenantModel
{
    use \app\traits\Signature;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'POSes';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['serial', 'mobile','is_active', 'location'], 'required'],
            [['mobile', 'created_at', 'created_by', 'updated_at', 'updated_by', 'is_active'], 'integer'],
            [['serial'], 'string', 'max' => 100],
            [['simcard', 'location'], 'string', 'max' => 45],
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
            'serial' => Yii::t('app', 'Serial'),
            'mobile' => Yii::t('app', 'Mobile'),
            'simcard' => Yii::t('app', 'SIM Card No.'),
            'location' => Yii::t('app', 'Location'),
            'created_at' => Yii::t('app', 'Created At'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'is_active' => Yii::t('app', 'Is Active'),
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
    public function getUpdatedBy()
    {
        return $this->hasOne(Staff::className(), ['mobile' => 'updated_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOwnerR()
    {
        return $this->hasOne(Staff::className(), ['mobile' => 'location']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTickets()
    {
        return $this->hasMany(Tickets::className(), ['machine_serial' => 'serial']);
    }
}
