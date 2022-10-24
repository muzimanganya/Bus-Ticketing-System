<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\WalletLog;

/**
 * WalletLogSearch represents the model behind the search form about `app\models\WalletLog`.
 */
class WalletLogSearch extends WalletLog
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'wallet', 'previous_balance', 'current_balance', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['ternant_db', 'reference', 'type', 'comment'], 'safe'],
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
        $query = WalletLog::find();

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
            'wallet' => $this->wallet,
            'previous_balance' => $this->previous_balance,
            'current_balance' => $this->current_balance,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'ternant_db', $this->ternant_db])
            ->andFilterWhere(['like', 'reference', $this->reference])
            ->andFilterWhere(['like', 'type', $this->type])
            ->andFilterWhere(['like', 'comment', $this->comment]);
            
        $query->orderBy('id DESC');

        return $dataProvider;
    }
}
