<?php

namespace app\modules\v1\controllers;

use app\models\Wallet;
use yii\data\ActiveDataProvider;
use Yii;

class WalletsController extends ApiController
{
    public $modelClass = 'app\models\Wallet';
    
    
    public function actionBalance($currency)
    {
        $query = Wallet::find()
                 //->with('children')
                 ->select(['currency', 'current_amount', 'last_recharged'])
                 ->where(['owner'=>Yii::$app->user->id, 'currency'=>$currency]);
                    
        return new ActiveDataProvider([
            'query'=>$query
        ]);
    }
}