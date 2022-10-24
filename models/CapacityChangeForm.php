<?php

namespace app\models;

class CapacityChangeForm extends \yii\base\Model
{
    public $capacity;
    public $comment;
    public $pk;

    public function rules()
    {
        return [
            [['capacity', 'comment', 'pk'], 'required'],
            [['comment', 'pk'], 'string'],
            [['capacity'], 'integer']
        ];
    }
}