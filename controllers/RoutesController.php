<?php

namespace app\controllers;

use Yii;
use yii\web\UploadedFile;
use app\models\Route;
use app\models\RouteSearch;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * RoutesController implements the CRUD actions for Route model.
 */
class RoutesController extends TenantController
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
     * Lists all Route models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new RouteSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Route model.
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
     * Creates a new Route model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Route();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Route model.
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
     * Deletes an existing Route model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }
    
    public function actionUploadPricing()
    {
        $model = new \app\models\UploadPricing();

        if ($model->load(Yii::$app->request->post())) {
            $model->file = UploadedFile::getInstance($model, 'file');
            if ($model->validate()) {
                $data = $model->readFile();
                //delete them in database
                $this->db->createCommand()->delete('Pricing', ['route'=>$model->route])->execute();                //add new ones
                
                //recreate
                $this->db->transaction(function ($db) use ($data, $model) {
                    foreach ($data as $datum) {
                        $stops = explode('-', $datum[0]);
                        
                        $db->createCommand()->insert('Pricing', [
                            'start'=>$stops[0],
                            'end'=>$stops[1],
                            'route'=>$model->route,
                            'price'=>$datum[1],
                            'currency'=>$datum[2],
                            'created_at'=> time(),
                            'created_by'=>Yii::$app->user->id,
                            'updated_at'=>time(),
                            'updated_by'=>Yii::$app->user->id,
                        ])->execute();
                    }
                });
                return $this->redirect(['view', 'id'=>$model->route]);
            }
        }
        
        return $this->render('_uploadPricing', [
            'model' => $model,
        ]);
    }

    /**
     * Finds the Route model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Route the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Route::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
    public function beforeAction($action)
    {
        if(!Yii::$app->user->identity->isSuperAdmin())
            throw new ForbiddenHttpException('You are not allowed to access this Page.');
        else
            return parent::beforeAction($action);
    }
}
