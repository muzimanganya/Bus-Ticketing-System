<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\RouteCard;

/**
 * RouteCardSearch represents the model behind the search form about `app\models\RouteCard`.
 */
class RouteCardSearch extends RouteCard
{
    public function init()
    {
        $return = parent::init();
        if ($this->isNewRecord) {
            $this->currency = null;
            $this->total_trips = null;
        }
        
        return $return;
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['card', 'remaining_trips', 'price', 'total_trips', 'created_at', 'created_by', 'updated_at', 'updated_by','sold_by'], 'integer'],
            [['start', 'end', 'currency', 'owner','phone'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = RouteCard::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'card' => $this->card,
            'remaining_trips' => $this->remaining_trips,
            'price' => $this->price,
            'total_trips' => $this->total_trips,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'start', $this->start])
            ->andFilterWhere(['like', 'end', $this->end])
            ->andFilterWhere(['like', 'currency', $this->currency])
		->andFilterWhere(['like', 'phone', $this->phone])
            ->andFilterWhere(['like', 'owner', $this->owner]);

        return $dataProvider;
    }
    
    public function searchDailySales($start, $end, $user)
    {
        $query = RouteCard::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
 
        // grid filtering conditions
        $query->where([
            'is_sold' => 1
        ])
        ->andWhere(['BETWEEN', 'DATE(FROM_UNIXTIME(sold_at))', $start, $end]);
        
        if (!empty($user)) {
            $query->andWhere(['sold_by'=>$user]);
        }

        return $dataProvider;
    }
}
