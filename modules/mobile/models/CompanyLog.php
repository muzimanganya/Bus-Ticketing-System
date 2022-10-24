<?php

namespace app\modules\mobile\models;

use app\models\Staff;

use Yii;

/**
 * This is the model class for table "CompanyLogs".
 *
 * @property integer $id
 * @property integer $company
 * @property string $reference
 * @property string $type
 * @property string $comment
 * @property integer $created_at
 * @property integer $created_by
 * @property integer $updated_at
 * @property integer $updated_by
 * @property string $ternant_db
 *
 * @property Staffs $createdBy
 * @property Companies $company0
 * @property Staffs $updatedBy
 */
class CompanyLog extends \yii\db\ActiveRecord
{
    use \app\traits\Signature;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'CompanyLogs';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company', 'reference','ternant_db', 'amount', 'change'], 'required'],
            [['company', 'created_at', 'created_by', 'updated_at', 'updated_by', 'amount', 'change'], 'integer'],
            [['reference', 'ternant_db'], 'string', 'max' => 45],
            [['type'], 'string', 'max' => 2],
            [['comment'], 'string', 'max' => 150],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => Staff::className(), 'targetAttribute' => ['created_by' => 'mobile']],
            [['company'], 'exist', 'skipOnError' => true, 'targetClass' => Company::className(), 'targetAttribute' => ['company' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => Staff::className(), 'targetAttribute' => ['updated_by' => 'mobile']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'company' => Yii::t('app', 'Company'),
            'reference' => Yii::t('app', 'Reference'),
            'type' => Yii::t('app', 'Type'),
            'typeStr' => Yii::t('app', 'Type'),
            'comment' => Yii::t('app', 'Comment'),
            'created_at' => Yii::t('app', 'Created'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_at' => Yii::t('app', 'Updated'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'ternant_db' => Yii::t('app', 'Service'),
            'change' => Yii::t('app', 'Transaction'),
            'amount' => Yii::t('app', 'Balance'),
        ];
    }
    
    public function getTypeStr()
    {
        if ($this->type == 'TO') {
            return 'Top Up';
        }
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
    public function getCompanyR()
    {
        return $this->hasOne(Company::className(), ['id' => 'company']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(Staff::className(), ['mobile' => 'updated_by']);
    }
}
