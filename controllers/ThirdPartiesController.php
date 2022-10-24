<?php

namespace app\controllers;

use Yii;
use app\models\ThirdParty;
use app\modules\mobile\models\ThirdPartySearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * ThirdPartiesController implements the CRUD actions for ThirdParty model.
 */
class ThirdPartiesController extends Controller
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
     * Lists all ThirdParty models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ThirdPartySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single ThirdParty model.
     * @param string $tenant
     * @param string $database
     * @return mixed
     */
    public function actionView($tenant, $database)
    {
        return $this->render('view', [
            'model' => $this->findModel($tenant, $database),
        ]);
    }

    /**
     * Creates a new ThirdParty model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ThirdParty();
        $isLoaded = $model->load(Yii::$app->request->post());
        $model->logoFile = UploadedFile::getInstance($model, 'logoFile');

        if ($isLoaded && $model->validate()) 
        {
            $model->upload();
            // file is uploaded successfully
            if($model->save(false))
            {
                return $this->redirect(['view', 'tenant' => $model->tenant, 'database' => $model->database]);
            }
        }
        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing ThirdParty model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $tenant
     * @param string $database
     * @return mixed
     */
    public function actionUpdate($tenant, $database)
    {
        $model = $this->findModel($tenant, $database);

        $isLoaded = $model->load(Yii::$app->request->post());
        $model->logoFile = UploadedFile::getInstance($model, 'logoFile');

        if ($isLoaded && $model->validate()) 
        {
            $model->upload();
            // file is uploaded successfully
            if($model->save(false))
            {
                return $this->redirect(['view', 'tenant' => $model->tenant, 'database' => $model->database]);
            }
        }
        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing ThirdParty model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $tenant
     * @param string $database
     * @return mixed
     */
    public function actionDelete($tenant, $database)
    {
        $this->findModel($tenant, $database)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the ThirdParty model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $tenant
     * @param string $database
     * @return ThirdParty the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($tenant, $database)
    {
        if (($model = ThirdParty::findOne(['tenant' => $tenant, 'database' => $database])) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
