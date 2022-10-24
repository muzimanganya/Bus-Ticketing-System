<?php

namespace app\models;

class DeletedTicketSearch extends \app\models\TicketSearch
{

    public static function tableName()
    {
        return 'DeletedTickets';
    }

    public function search($params)
    {
        $query = self::find();
        return parent::search($params, $query);
    }

}