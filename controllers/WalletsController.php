<?php

namespace app\controllers;

use Yii;
use app\models\WalletLog;
use app\models\Wallet;
use app\models\WalletSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * WalletsController implements the CRUD actions for Wallet model.
 */
class WalletsController extends Controller
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
     * Lists all Wallet models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new WalletSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Wallet model.
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
     * Creates a new Wallet model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Wallet();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Wallet model.
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
     * Deletes an existing Wallet model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }
    
    public function actionTopup()
    {
        $model = new \app\models\TopUp();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                $wallet = Wallet::findOne($model->wallet);
                if($wallet)
                {
                    $prev = $wallet->current_amount;
                    $new = $prev+$model->amount;
                    
                    $walletLog = new WalletLog;
                    $walletLog->current_balance = $new;
                    $walletLog->previous_balance = $prev;
                    $walletLog->wallet = $wallet->id;
                    $walletLog->type = WalletLog::ACTION_TOPUP;
                    $walletLog->reference = $model->reference;
                    $walletLog->ternant_db = Yii::$app->user->identity->tenant_db;
                    if($walletLog->save())
                    {
                        $wallet->current_amount = $new;
                        $wallet->last_recharged = $model->amount;
                        if($wallet->save())
                        {
                            return $this->redirect(['/wallets/index']);
                        }
                        else
                            $walletLog->delete(); //no point of having log but no record really
                    }
                    else
                    {
                        $model->addError('wallet', Yii::t('app', 'Saving to the log failed'));
                        Yii::error('top-up-error', json_encode($walletLog->errors));
                    }
                }
                else
                    $model->addError('wallet', Yii::t('app', 'Wallet not found'));
            }
        }

        return $this->render('topup', [
            'model' => $model,
        ]);
    }

    /**
     * Finds the Wallet model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Wallet the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Wallet::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
