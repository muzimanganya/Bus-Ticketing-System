<?php
namespace app\models;

use Yii;
use yii\base\Model;

class ChangeCustomerBus extends Model
{
    public $to_time;
    public $to_date;
    
    public $ticket;
    public $seat;
    public $comment;
    
    public function rules()
    {
        return [
            [['to_date','to_time', 'ticket', 'seat'], 'required'],
            [['to_time','to_date'], 'string'],
            [['seat'], 'integer'],
            [['seat'], 'validateMove'],
            [['comment', 'ticket'], 'string'],
            [['ticket'], 'exist', 'skipOnError' => false, 'targetClass' => Ticket::className(), 'targetAttribute' => ['ticket' => 'ticket']],
        ];
    }
    
    public function attributeLabels()
    {
        return [
            'to_time' => Yii::t('app', 'Change to Time'),
            'customer' => Yii::t('app', 'Customer Number'),
            'to_date' => Yii::t('app', 'Move to Date'),
            'to_time' => Yii::t('app', 'Move to Time'),
            'seat' => Yii::t('app', 'Move to Seat'),
        ];
    }
    
    public function validateMove()
    {
        $ticket = Ticket::find()->where(['ticket'=>$this->ticket])->one();
        
        //invalid Ticket
        if(empty($ticket))
            $this->addError('ticket', 'No Such Ticket');
        else
        {
            //check if bus and hour exists
            $plannedRoute = PlannedRoute::find()->where([
                'route'=>$ticket->route,
                'dept_date'=>$this->to_date,
                'dept_time'=>$this->to_time , 
                'is_active'=>1
            ])->one();
            
            if(!$plannedRoute)
                $this->addError('to_time', 'No Destination Bus for that Hour');
            else 
            {
                //check if seat is already occupied
                if(Ticket::find()->where([
                    'start'=>$ticket->start, 
                    'end'=>$ticket->end, 
                    'route'=>$plannedRoute->route,
                    'dept_date'=>$this->to_date,
                    'dept_time'=>$this->to_time,
                    'seat'=>$this->seat,
                ])->exists())
                    $this->addError('seat', 'The seat is already occupied!');
            }     
        }
    }
}
