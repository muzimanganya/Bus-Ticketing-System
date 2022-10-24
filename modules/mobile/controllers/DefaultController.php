<?php

namespace app\modules\mobile\controllers;

use app\modules\mobile\models\LoginForm;
use app\modules\mobile\models\CompanyLogSearch;
use Yii;

/**
 * Default controller for the `mobile` module
 */
class DefaultController extends BaseController
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new CompanyLogSearch();
        $searchModel->company = Yii::$app->user->id;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    
    public function actionLogin()
    {
        $this->layout = '//login';
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        
        $model = new LoginForm();
        
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->redirect(['/mobile']);
        }
        return $this->render('login', [
            'model' => $model,
        ]);
    }
    
    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->redirect(['/mobile']);
    }
}
