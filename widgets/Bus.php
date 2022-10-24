<?php 
namespace app\widgets;

use yii\base\Widget;
use yii\helpers\Html;
use Yii; 

class Bus extends Widget
{
    public $model;
    public $seats;// ['owner'=>'...', 'ticket'=>'...', 'status']

    public function init()
    {
        parent::init();
        $modelClass = "app\\models\\Bus";
        if ($this->model === null || !(($this->model) instanceof $modelClass)) 
            throw new \yii\base\InvalidConfigException(Yii::t('app', 'Model Must be defined and must be of type app\\models\\Bus'));
        
        $this->view->registerCss('
            .bus-table td {
                text-align: center;   
            }

            .black{
                background-color:rgba(0, 0, 0, 0.8);
                color:#ffffff;
                font-size:16px;
            }

            .fit{
                white-space: nowrap;
                width: 1%;
            }
        ');
    }

    public function run()
    {
        $model = $this->model;

        $total  = $model->total_seats;
        $left  = $model->leftseats;
        $right  = $model->rightseats;
        $back  = $model->backseats;

        $rows = ($total - $back)/($left+$right);
        //build the table
        $table = '<table class="fit bus-table black table table-bordered">';
        $ctable = '</table>';
        $html = '';
        $seatno = 0;

        for($i=0;  $i<$rows; $i++)
        {
            $html .='<tr>';
            //add left
            for($l=1; $l<$left+1; $l++)
            {
                $seatno++; 
                $html = $html.'<td class="fit"><span class="btn btn-primary" id=seat_'.$seatno.'>'.$seatno.'</span></td>';
            } 
            //space 2 cols 
            $html = $html.'<td style="border:0 !important;"></td>';

            for($r=1; $r<$right+1; $r++)
            {
                $seatno++;
                $html = $html.'<td class="fit" ><span class="btn btn-primary" id=seat_'.$seatno.'>'.$seatno.'</span></td>';
            }
            $html .='</tr>';
        }
        //add last column
        $colspan = $left+$right+1;//1 is for empty path
        $html = $html."<tr> <td colspan=$colspan>";

        for($i=1; $i<=$back; $i++)
        {
            $seatno++;
            $html = $html.'<span class="btn btn-md btn-primary" id=seat_'.$seatno.'>'.$seatno.'</span>';
        }
        $html.='</td>';

        echo $table.$html.$ctable.'<br><br>';
    }
}
