<?php

namespace app\controllers;

use Yii;
use yii\db\Expression;
use app\models\TenantModel;
use app\models\PlanTemplate;
use app\models\PlanTemplateSearch;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\VerbFilter;

/**
 * PlanTemplatesController implements the CRUD actions for PlanTemplate model.
 */
class PlanTemplatesController extends TenantController
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
     * Lists all PlanTemplate models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PlanTemplateSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new PlanTemplate model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new PlanTemplate();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }
    
    
    public function actionPlan()
    {
        $model = new \app\models\GenPlanned();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                $days = cal_days_in_month(CAL_GREGORIAN, $model->month, date('Y'));
                
                $db = TenantModel::getDb();
                
                $db->transaction(function ($db) use ($model, $days) {
                    //delete olds stuffs
                    $cmd = $db->createCommand();
                    //see
                    //$sql = 'DELETE pr FROM PlannedRoutes AS pr LEFT JOIN Tickets AS t ON pr.dept_date = t.dept_date AND pr.dept_time  = t.dept_time AND pr.bus = t.bus WHERE  t.id IS NULL AND t.dept_date IS NULL AND t.dept_time IS NULL AND MONTH(pr.dept_date) = :month AND YEAR(pr.dept_date)=:year';
                    //$sql = 'DELETE FROM PlannedRoutes WHERE CONCAT(dept_date, dept_time) NOT IN (SELECT CONCAT(dept_date, dept_time) From Tickets WHERE MONTH(dept_date) = :month AND YEAR(dept_date)=:year) AND MONTH(dept_date) = :month AND YEAR(dept_date)=:year';
                    $sql = 'DELETE FROM PlannedRoutes WHERE MONTH(dept_date) = :month AND YEAR(dept_date)=:year AND (dept_date, dept_time, route, bus) NOT IN  (SELECT dept_date, dept_time, route, bus FROM  Tickets t  WHERE MONTH(t.dept_date) = :month AND YEAR(t.dept_date)=:year AND MONTH(t.dept_date) = :month AND YEAR(t.dept_date)=:year GROUP BY t.dept_date,t.dept_time)';
                    //$cmd->sql = $sql;
                    //$cmd->bindValue(':month', $model->month)
                    //    ->bindValue(':year',2019/* date('Y')*/)
                    //    ->execute();
                        

                    for ($day=1; $day<=$days; $day++) {
                        $templates = PlanTemplate::find()->asArray()->all();
                        
                       // $year = date('Y');
			$year='2022';
                        $month = $model->month;
                        $dept_date = "$year-$month-$day";
                        
                        foreach ($templates as $template) {
                            //insert plan
                            $sql = 'INSERT INTO PlannedRoutes(route, dept_date, dept_time, bus, priority, capacity, created_at, created_by, updated_at, updated_by) VALUES(:route, :dept_date, :dept_time, :bus, :priority, :capacity, :created_at, :created_by, :updated_at, :updated_by) ON DUPLICATE KEY UPDATE updated_at = :updated_at';
                            $db->createCommand($sql)->bindValues([
                                ':route' => $template['route'],
                                ':dept_date' => $dept_date,
                                ':dept_time' => $template['hour'],
                                ':bus' => $template['bus'],
                                ':priority' => 10,
                                ':capacity' => $template['pcapacity'],
                                ':created_at' => time(),
                                ':created_by' => Yii::$app->user->id,
                                ':updated_at' => time(),
                                ':updated_by' => Yii::$app->user->id,
                            ])->execute();
                        }
                    }
                });
                return $this->redirect(['/planned-routes/index']);
            }
        }

        return $this->render('_selmonth', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing PlanTemplate model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $route
     * @param string $bus
     * @param string $hour
     * @return mixed
     */
    public function actionUpdate($route, $bus, $hour)
    {
        $model = $this->findModel($route, $bus, $hour);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing PlanTemplate model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $route
     * @param string $bus
     * @param string $hour
     * @return mixed
     */
    public function actionDelete($route, $bus, $hour)
    {
        $this->findModel($route, $bus, $hour)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the PlanTemplate model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $route
     * @param string $bus
     * @param string $hour
     * @return PlanTemplate the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($route, $bus, $hour)
    {
        if (($model = PlanTemplate::findOne(['route' => $route, 'bus' => $bus, 'hour' => $hour])) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
    
    public function beforeAction($action)
    {
        if(!Yii::$app->user->identity->isManager())
            throw new ForbiddenHttpException('You are not allowed to access this Page.');
        else
            return parent::beforeAction($action);
    }
}

