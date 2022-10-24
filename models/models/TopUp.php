<?php

namespace app\models;

class TopUp extends \yii\base\Model
{
    public $wallet;
    public $amount;
    public $reference;
    
    
    public function rules()
    {
        return [
            [['wallet', 'amount', 'reference'], 'required'],
            [['wallet', 'reference'], 'string'],
            ['amount', 'number']
        ]; 
    }

}