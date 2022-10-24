<?php
namespace app\controllers;

use Yii;
use yii\web\ForbiddenHttpException;
use app\models\TenantModel;

use yii\web\Controller;

class TenantController extends Controller
{
    public $db;
    
    public function beforeAction($action)
    {
        $return = parent::beforeAction($action);
        
        $this->db = Yii::$app->user->isGuest? Yii::$app->db : TenantModel::getDb();
        
        if (Yii::$app->user->identity && !Yii::$app->user->identity->isAdmin() && $action->id!='error'&&$action->id!='login') {
            throw new ForbiddenHttpException('You don\'t have enough permission to access this page');
        } elseif (Yii::$app->user->identity && Yii::$app->user->identity->is_active==0 && $action->id!='error'&&$action->id!='login'&&$action->id!='logout') {
            throw new ForbiddenHttpException('You are suspended. Please contact System admin');
        }

        /*if (!Yii::$app->user->identity->isManager() && $action->controller->id == 'tickets') {
            throw new ForbiddenHttpException('You don\'t have enough permission to access this page');
        } */

        return $return;
    }
}
