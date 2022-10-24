<?php

namespace app\models;

use Yii;
use app\traits\Signature;

/**
 * This is the model class for table "WalletLogs".
 *
 * @property integer $id
 * @property integer $wallet
 * @property string $ternant_db
 * @property string $reference
 * @property string $type
 * @property integer $previous_balance
 * @property integer $current_balance
 * @property string $comment
 * @property integer $created_at
 * @property integer $created_by
 * @property integer $updated_at
 * @property integer $updated_by
 *
 * @property Staffs $createdBy
 * @property Staffs $updatedBy
 * @property Wallets $wallet0
 */
class WalletLog extends \yii\db\ActiveRecord
{
    const ACTION_TOPUP = 'TU';
    const ACTION_TICKET_SELLING = 'TS';

    use Signature;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'WalletLogs';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['wallet', 'ternant_db', 'reference', 'type', 'previous_balance', 'current_balance'], 'required'],
            [['wallet', 'previous_balance', 'current_balance', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['ternant_db', 'reference'], 'string', 'max' => 45],
            [['type'], 'string', 'max' => 2],
            [['comment'], 'string', 'max' => 150],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => Staff::className(), 'targetAttribute' => ['created_by' => 'mobile']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => Staff::className(), 'targetAttribute' => ['updated_by' => 'mobile']],
            [['wallet'], 'exist', 'skipOnError' => true, 'targetClass' => Wallet::className(), 'targetAttribute' => ['wallet' => 'id']],
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'wallet' => Yii::t('app', 'Wallet'),
            'ternant_db' => Yii::t('app', 'Company'),
            'reference' => Yii::t('app', 'Reference'),
            'type' => Yii::t('app', 'Type'),
            'previous_balance' => Yii::t('app', 'Previous Balance'),
            'current_balance' => Yii::t('app', 'Current Balance'),
            'comment' => Yii::t('app', 'Comment'),
            'created_at' => Yii::t('app', 'Created At'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'updated_by' => Yii::t('app', 'Updated By'),
        ];
    }
    
    public function getActionType()
    {
        if($this->type == self::ACTION_TOPUP)
            return Yii::t('app', 'Top Up');
        else if($this->type == self::ACTION_SELLING_TICKET)
            return Yii::t('app', 'Selling Ticket');
        else 
            return null;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(ThirdParty::className(), ['database' => 'ternant_db']);
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
    public function getWalletR()
    {
        return $this->hasOne(Wallet::className(), ['id' => 'wallet']);
    }
}
