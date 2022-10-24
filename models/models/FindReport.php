<?php
namespace app\models;

use yii\base\Model;
use Yii;

class FindReport extends Model
{
    public $report;
    public $start;
    public $end;
    public $reference;
    
    public function rules()
    {
        return [
            [['report', 'start', 'end'], 'required'],
            [['report', 'start', 'end', 'reference'], 'string']
        ];
    }
    
    public function attributeLabels()
    {
        return [
            'report' => Yii::t('app', 'Report'),
            'start' => Yii::t('app', 'Start Date'),
            'end' => Yii::t('app', 'End reportDate'),
        ];
    }
}
