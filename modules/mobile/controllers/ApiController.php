<?php

namespace app\modules\mobile\controllers;

use yii\rest\ActiveController;
use app\models\Ticket;
use app\modules\mobile\models\User;

use yii\helpers\ArrayHelper;
use Yii;

use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;

class ApiController extends ActiveController
{
    public $modelClass = 'app\models\Tickets';
    
    public $db;
    
    public function beforeAction($action)
    {
        $return = parent::beforeAction($action);
        $this->db = \app\models\TenantModel::getDb();
        return $return;
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            'except'=>['login'],
            'authMethods' => [
                HttpBearerAuth::className(),
                QueryParamAuth::className(),
            ],
        ];

        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();

        // disable the  actions
        unset($actions['delete'], $actions['create'], $actions['index'], $actions['view']);

        return $actions;
    }

    public function actionLogin()
    {
        $username = Yii::$app->request->post('username');
        $password = Yii::$app->request->post('password');
        
        $user = User::findByUsername($username);
        $success = ['success'=>false, 'token'=>''];
        if ($user && $user->validatePassword($password)) {
            $token = $user->generateToken();
            $success['success'] = true;
            $success['token'] = $token;
        }
        return $success;
    }
 
    public function actionBalance()
    {
        $company = Yii::$app->user->identity;
        $success = ['success'=>false];
        
        if ($company->is_active == 0) {
            $success['message'] = 'Wallet is locked';
        } else {
            $success['success'] = true;
            $success['previous_balance'] = $company->previous_balance;
            $success['current_balance'] = $company->current_balance;
        }
        return $success;
    }

    public function actionPayBooking($id, $pos)
    {
    }

   
    public function actionReserve()
    {
        try {
            $success = [];
            $data = Yii::$app->request->post();
            
            if (empty($data)) {
                $data = json_decode(file_get_contents('php://input'), true);
            }
            
            if (is_null($data)) {
                $success['success'] = false;
                $success['message'] = 'invalid JSON Request';
            } else {
                $tenant = $data['division'];
                if ($tenant=='rwanda') {
                    $tenant = 'volcano_rwanda';
                } elseif ($tenant=='burundi') {
                    $tenant = 'volcano_burundi';
                }
                if ($tenant=='intl') {
                    $tenant = 'volcano_uganda';
                }
                    
                //any user in database selling on behalf of the company?
                $user = \app\models\User::find()->where(['company'=>Yii::$app->user->id, 'tenant_db'=>$tenant])->one(); 
                if (empty($user)) {
                    $success['success'] = false;
                    $success['message'] = 'No valid seller for this company';
                } else {
                    Yii::$app->user->switchIdentity($user);
                    $result =  Yii::$app->runAction('/v1/tickets/tickets', ['action'=>'booking', 'json'=>$data]);
                    $result['division'] = $data['division'];
                    $success['success'] = true;
                    $success['message'] = $result;
                    
                }
                return $success;
            }
        } catch (\Exception $e) {
            throw $e;
            return ['success'=>false, 'message'=>'wicked error or some attributes missing'];
        }
    }
}
