<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Stops".
 *
 * @property string $name
 * @property string $country
 * @property integer $created_at
 * @property integer $created_by
 * @property integer $updated_at
 * @property integer $updated_by
 *
 * @property Routes[] $routes
 * @property Routes[] $routes0
 * @property Staffs $updatedBy
 * @property Staffs $createdBy
 */
class Stop extends TenantModel
{
    use \app\traits\Signature;

    public $countries = ['RW' => 'Rwanda','BU'=>'Burundi', 'UG'=>'Uganda', 'KE'=>'Kenya', 'TZ'=>'Tanzania'];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Stops';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'country'], 'required'],
            [['created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['name'], 'string', 'max' => 100],
            [['country'], 'string', 'max' => 45],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => Staff::className(), 'targetAttribute' => ['updated_by' => 'mobile']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => Staff::className(), 'targetAttribute' => ['created_by' => 'mobile']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('app', 'Name'),
            'country' => Yii::t('app', 'Country'),
            'stopCountry' => Yii::t('app', 'Country'),
            'created_at' => Yii::t('app', 'Created'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_at' => Yii::t('app', 'Updated'),
            'updated_by' => Yii::t('app', 'Updated By'),
        ];
    }

    public function getStopCountry()
    {
        return strlen($this->country)>2 ? $this->country : $this->countries[ucfirst($this->country)];
    }
    
    public function getFullname()
    {
        return $this->name.' - '.$this->countries[$this->country];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRoutes()
    {
        return $this->hasMany(Routes::className(), ['end' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRoutes0()
    {
        return $this->hasMany(Routes::className(), ['start' => 'name']);
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
    public function getCreatedBy()
    {
        return $this->hasOne(Staff::className(), ['mobile' => 'created_by']);
    }
}
