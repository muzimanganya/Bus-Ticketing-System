<?php

namespace app\controllers;

use Yii;
use app\models\SysLog;
use app\models\PlannedRoute;
use app\models\PlannedRouteSearch;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\VerbFilter;

/**
 * PlannedRoutesController implements the CRUD actions for PlannedRoute model.
 */
class PlannedRoutesController extends TenantController
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
     * Lists all PlannedRoute models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PlannedRouteSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    
     /**
     * Displays a single PlannedRoute model.
     * @param integer $route
     * @param string $dept_date
     * @param string $dept_time
     * @param string $bus
     * @return mixed
     */
    public function actionView($route, $dept_date, $dept_time, $bus)
    {
        return $this->render('view', [
            'model' => $this->findModel($route, $dept_date, $dept_time, $bus),
        ]);
    }



    /**
     * Creates a new PlannedRoute model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new PlannedRoute();
        $isLoaded = $model->load(Yii::$app->request->post());
        if ($isLoaded) {
            //priority filled
            if (empty($model->priority)) {
                $model->priority = 10;
            }
            
            if (empty($model->capacity)) {
                $model->capacity = $this->db->createCommand('SELECT total_seats FROM Buses WHERE regno=:regno;')
                   ->bindValue(':regno', $model->bus)
                   ->queryScalar();
            }
              
            if ($model->save()) {
                return $this->redirect(['index']);
            }
        }
        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing PlannedRoute model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $start
     * @param string $end
     * @param string $dept_date
     * @param string $dept_time
     * @return mixed
     */
    public function actionUpdate($route, $dept_date, $dept_time, $bus)
    {
        $model = $this->findModel($route, $dept_date, $dept_time, $bus);
        $oldDate = $model->dept_date;
        $oldTime = $model->dept_time;
        $oldRoute = $model->routeR->start.'- '.$model->routeR->end;
        $oldCapacity = $model->capacity;
        $oldBus = $model->bus;
        $user = Yii::$app->user->identity;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $comment = "{$user->name}-{$user->mobile} Updated Bus $oldDate $oldTime of $oldRoute with Capacity $oldCapacity TO {$model->dept_date} {$model->dept_time} of {$model->routeR->start}-{$model->routeR->end} Capacity {$model->capacity} and comment:{$model->comment}";
            //save Log
            $log = new SysLog;
            $log->attributes = [
                'category'=>SysLog::LOG_CAT_CAPACITY_CHANGE,
                'reference'=>json_encode($model->getPrimaryKey()),
                'comment'=>$comment,
                'created_at'=>time(),
                'updated_at'=>time(),
                'created_by'=>Yii::$app->user->id,
                'updated_by'=>Yii::$app->user->id,
            ];
            $log->save();

            //did they change the bus? Move old bus's ticket too
            if($oldBus!=$model->bus)
			{
				PlannedRoute::getDb()->transaction(function($db) use($model, $oldBus) {
				    $db->createCommand('UPDATE Tickets SET bus=:bus WHERE dept_date=:dept_date AND dept_time=:dept_time AND route=:route AND bus=:oldbus')
						   ->bindValue(':bus', $model->bus)
						   ->bindValue(':dept_date', $model->dept_date)
						   ->bindValue(':dept_time', $model->dept_time)
						   ->bindValue(':route', $model->route)
						   ->bindValue(':oldbus', $oldBus)
						   ->execute();

					$db->createCommand('UPDATE ReservedSeats SET bus=:bus WHERE dept_date=:dept_date AND dept_time=:dept_time AND route=:route AND bus=:oldbus')
						   ->bindValue(':bus', $model->bus)
						   ->bindValue(':dept_date', $model->dept_date)
						   ->bindValue(':dept_time', $model->dept_time)
						   ->bindValue(':route', $model->route)
						   ->bindValue(':oldbus', $oldBus)
						   ->execute();

				});
			}
            
            return $this->redirect(['index']);
        }
        return $this->render('update', [
            'model' => $model,
        ]);
    }
     
    public function actionChangeCapacity()
    {
        //$route, $dept_date, $dept_time, $bus
        $model = $this->findModel($route, $dept_date, $dept_time, $bus);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing PlannedRoute model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $start
     * @param string $end
     * @param string $dept_date
     * @param string $dept_time
     * @return mixed
     */
    public function actionDelete($route, $dept_date, $dept_time, $bus)
    {
        $this->findModel($route, $dept_date, $dept_time, $bus)->delete();

        return $this->redirect(['index']);
    }
    
    public function actionMark()
    {
        //{id:{"route":1,"dept_date":"2017-04-30","dept_time":"06H00","bus":"BU123TA"}
        $data = json_decode(Yii::$app->request->post('id'), true);

        $route = $data['route'];
        $dept_date = $data['dept_date'];
        $dept_time = $data['dept_time'];
        $bus = $data['bus'];
        
        $isChecked = Yii::$app->request->post('checked');
        $model = PlannedRoute::find()->where([
            'route' => $route,
            'dept_date'=>$dept_date ,
            'dept_time'=>$dept_time ,
            'bus'=>$bus ])->one();
        //check status
        $success = ['success'=>false, 'msg'=>'Could not activate/deactivate Route'];
        if ($model) {
            $model->is_active = $isChecked ;

            if ($model->save()) {
                $success['success'] = true;
                $success['msg'] = $model->is_active==0? 'Bus Locked. No more sales': 'Bus unlocked. You can now sell';
            } else {
                $success['msg'] = $model->errors;
            }
        }
        return json_encode($success);
    }

    /**
     * Finds the PlannedRoute model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $start
     * @param string $end
     * @param string $dept_date
     * @param string $dept_time
     * @return PlannedRoute the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($route, $dept_date, $dept_time, $bus)
    {
        if (($model = PlannedRoute::findOne([
            'route' => $route,
            'dept_date' => $dept_date,
            'dept_time' => $dept_time,
            'bus' => $bus,
        ])) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
    
    public function beforeAction($action)
    {
        if(!Yii::$app->user->identity->isAdmin())
            throw new ForbiddenHttpException('You are not allowed to access this Page.');
        else
            return parent::beforeAction($action);
    }
}
