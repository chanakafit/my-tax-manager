<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * LiabilitySearch represents the model behind the search form of `app\models\Liability`.
 */
class LiabilitySearch extends Liability
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['liability_type', 'liability_category', 'lender_name', 'description', 'start_date', 'end_date', 'status', 'settlement_date', 'notes'], 'safe'],
            [['original_amount', 'interest_rate', 'monthly_payment'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
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
        $query = Liability::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC]
            ],
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
            'original_amount' => $this->original_amount,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'interest_rate' => $this->interest_rate,
            'monthly_payment' => $this->monthly_payment,
            'settlement_date' => $this->settlement_date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'liability_type', $this->liability_type])
            ->andFilterWhere(['like', 'liability_category', $this->liability_category])
            ->andFilterWhere(['like', 'lender_name', $this->lender_name])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'status', $this->status])
            ->andFilterWhere(['like', 'notes', $this->notes]);

        return $dataProvider;
    }
}

