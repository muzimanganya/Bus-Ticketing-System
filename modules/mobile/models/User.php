<?php

namespace app\modules\mobile\models;

use Yii;

class User extends Company implements \yii\web\IdentityInterface
{
    public $tenant_db = null;
    
    public function isAdmin()
    {
        return false;
    }
    
    public function isManager()
    {
        return false;
    }
    
    public function isSuperAdmin()
    {
        return false;
    }
    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return self::findOne($id);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return self::find()->where(['token'=>$token])->one();
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return self::find()->where(['name'=>$username])->one();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->token;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->token === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->getSecurity()->validatePassword($password, $this->password);
    }

    public function generateToken()
    {
        $this->token = Yii::$app->getSecurity()->generateRandomString();
        $this->token = hash('sha256',$this->token);
        
        //don't update the updated by nor updated at as we are really not updating
        $this->detachBehavior('blame');
        $this->detachBehavior('time');
         
        $this->save(false);
        return $this->token;
    }

    public function nullToken()
    {
        $this->token = null;
        $this->save(false);
    }
}
