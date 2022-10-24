<?php
namespace app\controllers;

use Yii;
use app\models\BoardingTime;
use app\models\BoardingTimeSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\VerbFilter;

/**
 * BoardingTimesController implements the CRUD actions for BoardingTime model.
 */
class BoardingTimesController extends Controller
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
     * Lists all BoardingTime models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new BoardingTimeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single BoardingTime model.
     * @param integer $route
     * @param string $start
     * @param string $end
     * @return mixed
     */
    public function actionView($route, $start, $end)
    {
        return $this->render('view', [
            'model' => $this->findModel($route, $start, $end),
        ]);
    }

    /**
     * Creates a new BoardingTime model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new BoardingTime();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing BoardingTime model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $route
     * @param string $start
     * @param string $end
     * @return mixed
     */
    public function actionUpdate($route, $start, $end)
    {
        $model = $this->findModel($route, $start, $end);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing BoardingTime model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $route
     * @param string $start
     * @param string $end
     * @return mixed
     */
    public function actionDelete($route, $start, $end)
    {
        $this->findModel($route, $start, $end)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the BoardingTime model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $route
     * @param string $start
     * @param string $end
     * @return BoardingTime the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($route, $start, $end)
    {
        if (($model = BoardingTime::findOne(['route' => $route, 'start' => $start, 'end' => $end])) !== null) {
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
