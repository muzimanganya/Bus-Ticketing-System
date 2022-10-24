<?php

namespace app\modules\mobile\models;

class TopUp extends \yii\base\Model
{
    public $company;
    public $amount;
    public $reference;
    
    public function rules()
    {
        return [
            [['amount', 'company', 'reference'], 'required']
        ];
    }
}
