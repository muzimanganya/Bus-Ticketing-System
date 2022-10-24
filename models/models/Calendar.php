<?php

namespace app\models;

class Calendar extends \yii\base\Model 
{
    public $month;
    public $year;
    
    private $months = ['Select Month', 'January', 'February','March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    
    
    public function rules()
    {
        return [
            [['month', 'year'], 'required'],
            [['month', 'year'], 'integer']
        ];
    }
    
    public function getYears()
    {
        $start = date('Y');
        $end = $start-5;
        $years = [];
        
        for($i= $end; $i<=$start; $i++)
            $years[$i] = $i;
            
        return $years;
    }

    public function getCurrentMonth()
    {
        return $this->months[intval($this->month)];
    }
    
    public function getMonths()
    {
        $map = [];
        foreach($this->months as $key=>$value)
            $map[$key] = $value;
        
        return $map;
    }
}