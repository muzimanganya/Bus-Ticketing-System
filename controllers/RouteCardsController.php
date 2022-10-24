<?php

namespace app\controllers;

use Yii;
use yii\web\ForbiddenHttpException;
use app\controllers\TenantController;
use app\models\RouteCard;
use app\models\RouteCardSearch;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * RouteCardsController implements the CRUD actions for RouteCard model.
 */
class RouteCardsController extends TenantController
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
     * Lists all RouteCard models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new RouteCardSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single RouteCard model.
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
     * Creates a new RouteCard model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new RouteCard();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            //if Multiple other cards are available add them also
            if (!RouteCard::find()->where(['card'=>$model->card])->exists()) {
                $model->save(false);
            }
            
            
            if (!empty($model->multicard)) {
                $cards = explode(',', $model->multicard);
                foreach ($cards as $card) {
                    if (RouteCard::find()->where(['card'=>$card])->exists()) {
                        continue;
                    }
                        
                    $cmodel = new RouteCard();
                    $cmodel->attributes = $model->attributes;
                    $cmodel->is_sold = 0;
                    $cmodel->card = $card;
                    $cmodel->save(false);
                }
            }
            return $this->redirect(['view', 'id' => $model->card]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing RouteCard model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        
        //prevent editing the card that is already sold
        if ($model->is_sold==1) {
            throw new ForbiddenHttpException('You cannot update Sold Card');
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->card]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing RouteCard model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the RouteCard model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return RouteCard the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = RouteCard::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
