<?php

namespace app\controllers;

use Yii;
use app\modules\mobile\models\CompanyLog;
use app\modules\mobile\models\CompanyLogSearch;
use app\modules\mobile\models\Company;
use app\modules\mobile\models\CompanySearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * MobileController implements the CRUD actions for Company model.
 */
class MobileMoneyController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Company models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CompanySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Company model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Company model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Company();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Company model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Company model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }
    
    //-------------
    // Non Company Actions
    //-------------
    public function actionTopup()
    {
        $model = new \app\modules\mobile\models\TopUp();

        if ($model->load(Yii::$app->request->post())) {
            $company = $this->findModel($model->company);
            
            if ($model->validate()) {
                $log = new CompanyLog;
                $log->attributes = [
                    'company'=> $model->company,
                    'reference'=>$model->reference,
                    'type'=>'TO',
                    'comment'=>'',
                    'ternant_db'=>'volcano_rwanda',
                    'change' => $model->amount,
                    'amount' => $company->current_balance
                ];
                if ($log->save()) {
                    $company->previous_balance = $company->current_balance;
                    $company->current_balance = $company->current_balance+$model->amount;
                    $company->save();
                    return $this->redirect(['/mobile-money']);
                } else {
                    //$model->addError('company', $company->getFirstError('company'));
                    //$model->addError('company', $company->getFirstError('reference'));
                    $model->addError('company', json_encode($company->errors));
                }
            }
        }

        return $this->render('topup', [
            'model' => $model,
        ]);
    }


    /**
     * Lists all CompanyLog models.
     * @return mixed
     */
    public function actionHistory()
    {
        $searchModel = new CompanyLogSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('history', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    /**
     * Finds the Company model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Company the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Company::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
