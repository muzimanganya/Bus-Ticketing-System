<?php
namespace app\traits;

use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

trait Signature
{
    public function behaviors()
    {
        return [
            'blame' => [
                'class' => BlameableBehavior::className(),
            ],
            'time' => [
                'class' => TimestampBehavior::className(),
            ],
        ];
    }
}