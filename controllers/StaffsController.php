<?php

namespace app\controllers;

use Yii;
use app\models\Staff;
use app\models\StaffSearch;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * StaffsController implements the CRUD actions for Staff model.
 */
class StaffsController extends TenantController
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
    
    public function beforeAction($action)
    {
        if (!Yii::$app->user->identity->isSuperAdmin()) {
            throw new \yii\web\ForbiddenHttpException('You cannot access this page');
        }
        return parent::beforeAction($action);
    }

    /**
     * Lists all Staff models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new StaffSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Staff model.
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
     * Displays a single Staff model.
     * @param integer $id
     * @return mixed
     */
    public function actionProfile()
    {
        return $this->render('profile', [
            'model' => $this->findModel(Yii::$app->user->id),
        ]);
    }

    /**
     * Creates a new Staff model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Staff(['scenario'=>'register']);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->mobile]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Staff model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->mobile]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Staff model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }
    
    public function actionAllowedRoutes($mobile)
    {
        if (Yii::$app->request->isPost) {
            $uroutes = Yii::$app->request->post('uroutes');
            //clear all assignment
            $this->db->createCommand()->delete('SellableRoutes', ['staff'=>$mobile])->execute();
            foreach ($uroutes as $route) {
                $this->db->createCommand()->insert('SellableRoutes', [
                    'staff' => $mobile,
                    'route' => $route,
                    'created_by'=>Yii::$app->user->id,
                    'updated_by'=>Yii::$app->user->id,
                    'created_at'=>time(),
                    'updated_at'=>time(),
                ])->execute();
            }
        } else {
            $routes = $this->db->createCommand('SELECT id, CONCAT(start, " - ", end) AS name FROM Routes WHERE parent IS NULL')
                ->queryAll();
                
            $selection = $this->db->createCommand('SELECT route FROM SellableRoutes WHERE staff = :staff')
                ->bindValue('staff', $mobile)
                ->queryColumn();
            
            return $this->renderAjax('_user-routes', [
                'routes' => $routes,
                'mobile' => $mobile,
                'selection' => $selection,
            ]);
        }
    }

    /**
     * Finds the Staff model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Staff the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Staff::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
