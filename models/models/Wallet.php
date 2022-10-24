<?php

namespace app\models;

use Yii;
use app\traits\Signature;

/**
 * This is the model class for table "Wallets".
 *
 * @property integer $id
 * @property string $currency
 * @property integer $owner
 * @property double $current_amount
 * @property double $last_recharged
 * @property integer $created_at
 * @property integer $created_by
 * @property integer $updated_at
 * @property integer $updated_by
 *
 * @property WalletLogs[] $walletLogs
 * @property Staffs $createdBy
 * @property Staffs $updatedBy
 * @property Staffs $owner0
 */
class Wallet extends \yii\db\ActiveRecord
{
    use Signature;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Wallets';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['currency', 'owner'], 'required'],
            [['owner', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['current_amount', 'last_recharged'], 'number'],
            [['current_amount', 'last_recharged'], 'default', 'value'=>0],
            [['currency'], 'string', 'max' => 5],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => Staff::className(), 'targetAttribute' => ['created_by' => 'mobile']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => Staff::className(), 'targetAttribute' => ['updated_by' => 'mobile']],
            [['owner'], 'exist', 'skipOnError' => true, 'targetClass' => Staff::className(), 'targetAttribute' => ['owner' => 'mobile']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'currency' => Yii::t('app', 'Currency'),
            'owner' => Yii::t('app', 'Owner'),
            'current_amount' => Yii::t('app', 'Current Amount'),
            'last_recharged' => Yii::t('app', 'Last Recharged'),
            'created_at' => Yii::t('app', 'Created At'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'updated_by' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWalletLogs()
    {
        return $this->hasMany(WalletLogs::className(), ['wallet' => 'id']);
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
        return $this->hasOne(Staff::className(), ['mobile' => 'owner']);
    }
}
