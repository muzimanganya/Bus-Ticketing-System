<?php
namespace app\models;

use yii\base\Model;
use Yii;

class GenPlanned extends Model
{
    public $month;
    
    public function rules()
    {
        return [
            [['month'], 'required'],
        ];
    }
    
    public function attributeLabels()
    {
        return [
            'month' => Yii::t('app', 'Generate For Month'),
        ];
    }
}
