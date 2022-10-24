<?php

namespace app\modules\mobile\models;

use app\models\Staff;
use Yii;

/**
 * This is the model class for table "Companies".
 *
 * @property integer $id
 * @property string $machine_serial
 * @property string $name
 * @property integer $created_at
 * @property integer $created_by
 * @property integer $updated_at
 * @property integer $updated_by
 * @property string $url
 * @property string $token
 * @property integer $current_balance
 * @property integer $previous_balance
 *
 * @property Staffs $createdBy
 * @property Staffs $updatedBy
 * @property CompanyLogs[] $companyLogs
 */
class Company extends \yii\db\ActiveRecord
{
    use \app\traits\Signature;
    
    public $password_;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Companies';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['machine_serial', 'name','url'], 'required'],
            [['created_at', 'created_by', 'updated_at', 'updated_by', 'current_balance', 'previous_balance'], 'integer'],
            [['machine_serial', 'token', 'password', 'password_'], 'string', 'max' => 100],
            [['machine_serial'], 'unique'],
            [['name'], 'string', 'max' => 45],
            [['url'], 'string', 'max' => 200],
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
            'id' => Yii::t('app', 'ID'),
            'machine_serial' => Yii::t('app', 'Machine Serial'),
            'name' => Yii::t('app', 'Name'),
            'created_at' => Yii::t('app', 'Created'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_at' => Yii::t('app', 'Updated'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'url' => Yii::t('app', 'Url'),
            'token' => Yii::t('app', 'Token'),
            'current_balance' => Yii::t('app', 'Current Balance'),
            'previous_balance' => Yii::t('app', 'Previous Balance'),
        ];
    }
    
    public function beforeSave($insert)
    {
        if (!empty($this->password_)) {
            $this->password = Yii::$app->getSecurity()->generatePasswordHash($this->password_);
        }
        return parent::beforeSave($insert);
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
    public function getLogs()
    {
        return $this->hasMany(CompanyLog::className(), ['company' => 'id']);
    }
}
