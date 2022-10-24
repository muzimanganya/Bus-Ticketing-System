<?php

namespace app\models;

use Yii;

class User extends Staff implements \yii\web\IdentityInterface
{
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
        return self::find()->where(['auth_key'=>$token])->one();
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return self::findIdentity($username);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->mobile;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->auth_key === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->getSecurity()->validatePassword($password, $this->password_hash);
    }

    public function generateToken()
    {
        $this->auth_key = Yii::$app->getSecurity()->generateRandomString();
        $this->save(false);
        return $this->auth_key;
    }

    public function nullToken()
    {
        $this->auth_key = null;
        $this->save(false);
    }
}
