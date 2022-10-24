<?php

namespace app\controllers;

use Yii;
use app\models\Pricing;
use app\models\PricingSearch;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\TenantModel;

/**
 * PricingController implements the CRUD actions for Pricing model.
 */
class PricingController extends Controller
{
	public $db;
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
     * Lists all Pricing models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PricingSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Pricing model.
     * @param string $start
     * @param string $end
     * @param integer $route
     * @param string $currency
     * @return mixed
     */

    	public function actionList($start=null,$end=null)
    {
        $searchModel = new PricingSearch();
        $dataProvider = $searchModel->price(Yii::$app->request->queryParams,$start,$end);

        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($start, $end, $route, $currency)
    {
        return $this->render('view', [
            'model' => $this->findModel($start, $end, $route, $currency),
        ]);
    }

public function actionPrice($start, $end, $route, $currency)
    {
	$this->db = TenantModel::getDb();
        $model = $this->findModel($start, $end, $route, $currency);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
			$params = [':start' =>$start,':end' =>$end,'price' =>$model->price,'currency' =>$model->currency];
				$this->db->createCommand('UPDATE Pricing SET price=:price WHERE currency=:currency and ((start=:start AND end=:end) OR (start=:end AND end=:start)) ')
				->bindValues($params)
				->execute();
            return $this->redirect(['list', 'start' => $model->start, 'end' => $model->end]);
        } else {
            return $this->render('price', [
                'model' => $model,
            ]);
        }
    }

public function actionPricing()
    {
		$this->db = TenantModel::getDb();
		$posts = $this->db->createCommand('Select * from prices')
			->queryAll();
			//var_dump($posts);
			//die;
		if(!empty($posts))
		{
				foreach($posts as $row)
			{
				//var_dump($row);
				
				$params = [':start' =>$row['start'],':end' =>$row['end'],':price'=>$row['price']];
				 $this->db->createCommand('UPDATE Pricing SET price=:price WHERE (start=:start and end=:end) or(start=:end and end=:start)')
				->bindValues($params)
				->execute();
				
            }
            return $this->redirect(['index']);
        }
	

         return $this->redirect(['index']);
    }

    /**
     * Creates a new Pricing model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */

    public function actionCreate()
    {
        $model = new Pricing();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Pricing model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $start
     * @param string $end
     * @param integer $route
     * @param string $currency
     * @return mixed
     */
    public function actionUpdate($start, $end, $route, $currency)
    {
        $model = $this->findModel($start, $end, $route, $currency);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'start' => $model->start, 'end' => $model->end, 'route' => $model->route, 'currency' => $model->currency]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Pricing model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $start
     * @param string $end
     * @param integer $route
     * @param string $currency
     * @return mixed
     */
    public function actionDelete($start, $end, $route, $currency)
    {
        $this->findModel($start, $end, $route, $currency)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Pricing model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $start
     * @param string $end
     * @param integer $route
     * @param string $currency
     * @return Pricing the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($start, $end, $route, $currency)
    {
        if (($model = Pricing::findOne(['start' => $start, 'end' => $end, 'route' => $route, 'currency' => $currency])) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
   /* public function beforeAction($action)
    {
        if(!Yii::$app->user->identity->isSuperAdmin() || !Yii::$app->user->identity->isAdmin())
            throw new ForbiddenHttpException('You are not allowed to access this Page.');
        else
            return parent::beforeAction($action);
    }*/
}
