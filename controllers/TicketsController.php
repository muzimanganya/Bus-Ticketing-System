<?php

namespace app\controllers;

use Yii;
use app\models\Ticket;
use app\models\TicketSearch;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\VerbFilter;
use app\controllers\TenantController;
use app\models\DeletedTicketSearch;

/**
 * TicketsController implements the CRUD actions for Ticket model.
 */
class TicketsController extends TenantController
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
     * Lists all Ticket models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new TicketSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    
    public function actionDeleted()
    {
        $searchModel = new DeletedTicketSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('deleted', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

	public function actionMobileTickets()
    {
        $searchModel = new MobileTicketSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('mobile-tickets', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Deletes an existing Ticket model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete()
    {
        $keys = Yii::$app->request->post('keys');
        if(!empty($keys))
        {
            $success = false;
            $this->db->transaction(function($db) use ($keys, &$success) {
                foreach($keys as $key)
                {
                    $db->createCommand('INSERT DeletedTickets SELECT * FROM Tickets WHERE id=:id')
                    ->bindValue(':id', $key)
                    ->execute();
                    
                    //remove from Tickets table
                    $db->createCommand('DELETE FROM Tickets WHERE id=:id')
                    ->bindValue(':id', $key)
                    ->execute();
                }
                $success = true;
            });
            
            if($success)
            {
                return json_encode([
                    'success'=>true,
                    'message'=>Yii::t('app', 'Succesfully deleted {c} Tickets', ['c'=>count($keys)])
                ]);
            }
        }
        return json_encode([
            'success'=>false,
            'message'=>'failed to delete the Ticket'
        ]);
    }
    
    public function actionUndelete()
    {
        $keys = Yii::$app->request->post('keys');
        if(!empty($keys))
        {
            $success = false;
            $this->db->transaction(function($db) use ($keys, &$success) {
                foreach($keys as $key)
                {
                    $db->createCommand('INSERT Tickets SELECT * FROM DeletedTickets WHERE id=:id')
                    ->bindValue(':id', $key)
                    ->execute();
                    
                    //remove from Tickets table
                    $db->createCommand('DELETE FROM DeletedTickets WHERE id=:id')
                    ->bindValue(':id', $key)
                    ->execute();
                }
                $success = true;
            });
            
            if($success)
            {
                return json_encode([
                    'success'=>true,
                    'message'=>Yii::t('app', 'Succesfully restored {c} Tickets', ['c'=>count($keys)])
                ]);
            }
        }
        return json_encode([
            'success'=>false,
            'message'=>'failed to restore the Ticket'
        ]);
    }
    
    
    public function beforeAction($action)
    {
        if(!Yii::$app->user->identity->isManager())
            throw new ForbiddenHttpException('You are not allowed to access this Page.');
        else
            return parent::beforeAction($action);
    }
}
