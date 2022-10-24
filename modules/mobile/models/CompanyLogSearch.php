<?php

namespace app\modules\mobile\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\mobile\models\CompanyLog;

/**
 * CompanyLogSearch represents the model behind the search form about `app\modules\mobile\models\CompanyLog`.
 */
class CompanyLogSearch extends CompanyLog
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'company', 'created_at', 'created_by', 'updated_at', 'updated_by', 'amount', 'change'], 'integer'],
            [['reference', 'type', 'comment', 'ternant_db'], 'safe'],
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
        $query = CompanyLog::find();

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
            'id' => $this->id,
            'company' => $this->company,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'reference', $this->reference])
            ->andFilterWhere(['like', 'type', $this->type])
            ->andFilterWhere(['like', 'comment', $this->comment])
            ->andFilterWhere(['like', 'ternant_db', $this->ternant_db]);

        return $dataProvider;
    }
}
