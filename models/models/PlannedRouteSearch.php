<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\PlannedRoute;

/**
 * PlannedRouteSearch represents the model behind the search form about `app\models\PlannedRoute`.
 */
class PlannedRouteSearch extends PlannedRoute
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['route', 'capacity','created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['dept_date', 'dept_time', 'bus'], 'safe'],
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
    public function search($params, $start=null, $end=null)
    {
        $query = PlannedRoute::find();

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
            'route' => $this->route,
            'dept_date' => $this->dept_date,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
            'capacity' => $this->capacity,
        ]);

        $query->andFilterWhere(['like', 'dept_time', $this->dept_time])
            ->andFilterWhere(['like', 'bus', $this->bus]);
        if(!empty($start) && !empty($end))
        {
            $query->andWhere(['BETWEEN', 'dept_date', $start, $end]);
        }

        $query->orderBy('dept_date DESC, dept_time ASC');
        
        return $dataProvider;
    }
}
