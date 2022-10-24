<?php

namespace app\modules\v1\controllers;

use yii\rest\ActiveController;
use app\models\User;

/**
 * User Mgt controller for the `v1` module
 */
class AccountsController extends ActiveController
{
    public $modelClass = 'app\models\Staff';

    public function actions()
    {
        $actions = parent::actions();

        // disable the  actions
        unset($actions['delete'], $actions['create'], $actions['index'], $actions['view']);

        return $actions;
    }

    public function actionLogin($username, $password)
    {
        $user = User::findByUsername($username);
        $success = ['success'=>false, 'token'=>''];
        if ($user && $user->validatePassword($password)) {
            $token = $user->generateToken();
            $success['success'] = true;
            $success['token'] = $token;
            $success['payload'] = $token;
        }
        return $success;
    }

    public function actionLogout($token)
    {
        $user = User::findIdentityByAccessToken($token);
        $success = ['success'=>false, 'message'=>'Could not log out'];
        if ($user) {
            $token = $user->nullToken();
            $success['success'] = true;
            $success['message'] = 'Logged out';
        }
        return $success;
    }

    public function actionChangePassword($username, $old, $new)
    {
        $user = User::findByUsername($username);
        $success = ['success'=>false, 'token'=>''];
        if ($user && $user->validatePassword($old)) {
            //change password
            $user->password = $new;
            $user->generateHash();
            $token = $user->nullToken(); //this saves it all so need to call save on previous line

            $success['success'] = true;
            $success['token'] = $token;
        }
        return $success;
    }
}
