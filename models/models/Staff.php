<?php

namespace app\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "Staffs".
 *
 * @property integer $mobile
 * @property string $name
 * @property string $location
 * @property string $role
 * @property integer $created_at
 * @property integer $created_by
 * @property integer $updated_at
 * @property integer $updated_by
 * @property string $password
 * @property string $auth_key
 * @property string $password_hash
 * @property integer $is_active
 *
 * @property Buses[] $buses
 * @property Buses[] $buses0
 * @property Buses[] $buses1
 * @property Customers[] $customers
 * @property Customers[] $customers0
 * @property POSes[] $pOSes
 * @property POSes[] $pOSes0
 * @property POSes[] $pOSes1
 * @property Routes[] $routes
 * @property Routes[] $routes0
 * @property SMS[] $sMSs
 * @property SMS[] $sMSs0
 * @property Stops[] $stops
 * @property Stops[] $stops0
 */
class Staff extends \yii\db\ActiveRecord
{
    public $password;
    public $repeat_password;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Staffs';
    }
    
    public function behaviors()
    {
        return [
            BlameableBehavior::className(),
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['mobile', 'name', 'role', 'location', 'tenant_db', 'timezone'], 'required'],
            [['mobile', 'created_at', 'created_by', 'updated_at', 'updated_by', 'is_active', 'is_third_party', 'company'], 'integer'],
            [['name', 'location', 'role', 'password', 'tenant_db', 'timezone'], 'string', 'max' => 45],
            [['auth_key'], 'string', 'max' => 32],
            [['password', 'repeat_password'], 'string', 'max' => 100],
            [['password', 'repeat_password'], 'required', 'on'=>'register'],
            [['password_hash'], 'safe'],
            [['repeat_password'], 'compare',
                'compareAttribute' => 'password',
                'message'=>Yii::t('app', 'Repeated Password does not Match the Password'),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'mobile' => Yii::t('app', 'Mobile'),
            'tenant_db' => Yii::t('app', 'Your Division'),
            'name' => Yii::t('app', 'Name'),
            'location' => Yii::t('app', 'Location'),
            'role' => Yii::t('app', 'Role'),
            'created_at' => Yii::t('app', 'Created At'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'password' => Yii::t('app', 'Password'),
            'auth_key' => Yii::t('app', 'Auth Key'),
            'password_hash' => Yii::t('app', 'Password Hash'),
            'is_active' => Yii::t('app', 'Is Active'),
            'tenant_db' => Yii::t('app', 'Service'),
            'is_third_party' => Yii::t('app', 'Third Party'),
            'company' => Yii::t('app', 'Company'),
        ];
    }

    //public function scenarios()
    //{
        //$attributes = array_keys($this->attributes);
        //unset($attributes['password_hash']);

        //$scenarios = parent::scenarios();
        //$scenarios['register'] = $attributes;
        //return $scenarios;
    //}

    public function beforeSave($insert)
    {
        if ($this->scenario=='register') {
            $this->generateHash();
        } elseif (!empty($this->password)) {
            $this->generateHash();
        }

        return parent::beforeSave($insert);
    }
    
    public function afterSave($insert, $changedAttributes)
    {
        if ($insert) {
            //add all routes to the user
            $db = TenantModel::getDb();
            $db->transaction(function ($db) {
                $routes = $db->createCommand('SELECT id FROM Routes WHERE parent IS NULL')->queryColumn();
                foreach ($routes as $route) {
                    $db->createCommand()->insert('SellableRoutes', [
                        'route' => $route,
                        'staff' => $this->mobile,
                        'created_by' => Yii::$app->user->id,
                        'updated_by' => Yii::$app->user->id,
                        'created_at' => time(),
                        'updated_at' => time(),
                    ])->execute();
                }
            });
        }
        return parent::afterSave($insert, $changedAttributes);
    }

    public function generateHash()
    {
        $this->password_hash = Yii::$app->getSecurity()->generatePasswordHash($this->password);
    }

    public function getRoles()
    {
        $roles =  [
            'admin' =>'Administrator',
            'driver'=>'Driver',
            'reseller'=>'Reseller',
            'mobile'=>'Mobile Application',
        ];
        
        if (Yii::$app->user->identity->isSuperAdmin()) { //TDL check roles when saving the user too
            $roles['root'] = 'Super Admin';
            $roles['manager'] = 'Manager';
            $roles['director'] = 'Director';
        }
        return $roles;
    }
    
    public function getDivisions()
    {
        return [
            'volcano_rwanda' =>'Rwanda',
            'volcano_burundi' =>'Burundi',
            'volcano_uganda' =>'Uganda',
        ];
    }
    
    public function isReseller()
    {
        return $this->role =='reseller' || $this->isAdmin() || $this->role =='mobile';
    }
    
    public function isAdmin()
    {
        return $this->role == 'admin' || $this->isManager() || $this->isSuperAdmin();
    }
    
    public function isManager()
    {
        return $this->role == 'manager' || $this->isDirector();
    }
    
    public function isSuperAdmin()
    {
        return $this->role == 'root' || $this->isDirector();
    }
    
    public function isDirector()
    {
        return $this->role == 'director';
    }
    
    public function getStaffRole()
    {
        $roles = $this->getRoles();
        return $roles[$this->role];
    }

    public function getLogo()
    {
        $model = ThirdParty::find()->where(['database'=>$this->tenant_db])->one();
        if($model)
            return Yii::getAlias("{$model->logoUrl}{$model->logo}");
        else
            return '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBuses()
    {
        return $this->hasMany(Buses::className(), ['created_by' => 'mobile']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBuses0()
    {
        return $this->hasMany(Buses::className(), ['driver' => 'mobile']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBuses1()
    {
        return $this->hasMany(Buses::className(), ['updated_by' => 'mobile']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCustomers()
    {
        return $this->hasMany(Customers::className(), ['created_by' => 'mobile']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCustomers0()
    {
        return $this->hasMany(Customers::className(), ['updated_by' => 'mobile']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPOSes()
    {
        return $this->hasMany(POSes::className(), ['created_by' => 'mobile']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPOSes0()
    {
        return $this->hasMany(POSes::className(), ['updated_by' => 'mobile']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPOSes1()
    {
        return $this->hasMany(POSes::className(), ['owner' => 'mobile']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRoutes()
    {
        return $this->hasMany(Routes::className(), ['created_by' => 'mobile']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRoutes0()
    {
        return $this->hasMany(Routes::className(), ['updated_by' => 'mobile']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSMSs()
    {
        return $this->hasMany(SMS::className(), ['created_by' => 'mobile']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSMSs0()
    {
        return $this->hasMany(SMS::className(), ['updated_by' => 'mobile']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStops()
    {
        return $this->hasMany(Stops::className(), ['updated_by' => 'mobile']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStops0()
    {
        return $this->hasMany(Stops::className(), ['created_by' => 'mobile']);
    }
}
