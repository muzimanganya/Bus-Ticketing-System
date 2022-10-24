<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Bus;

/**
 * BusSearch represents the model behind the search form about `app\models\Bus`.
 */
class BusSearch extends Bus
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['regno', 'backseats', 'doorside'], 'safe'],
            [['leftseats', 'rightseats', 'driver', 'total_seats', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
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
        $query = Bus::find();

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
            'leftseats' => $this->leftseats,
            'rightseats' => $this->rightseats,
            'driver' => $this->driver,
            'total_seats' => $this->total_seats,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'regno', $this->regno])
            ->andFilterWhere(['like', 'backseats', $this->backseats])
            ->andFilterWhere(['like', 'doorside', $this->doorside]);

        return $dataProvider;
    }
}
