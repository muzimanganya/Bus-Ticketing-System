<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\PlanTemplate;

/**
 * PlanTemplateSearch represents the model behind the search form about `app\models\PlanTemplate`.
 */
class PlanTemplateSearch extends PlanTemplate
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['route'], 'integer'],
            [['bus', 'hour'], 'safe'],
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
        $query = PlanTemplate::find();

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
        ]);

        $query->andFilterWhere(['like', 'bus', $this->bus])
            ->andFilterWhere(['like', 'hour', $this->hour]);
            
        $query->orderBy('route ASC, hour ASC');

        return $dataProvider;
    }
}
