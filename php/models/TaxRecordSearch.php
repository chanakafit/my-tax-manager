<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class TaxRecordSearch extends TaxRecord
{
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['tax_period_start', 'tax_period_end', 'tax_type', 'payment_status', 'payment_date', 'reference_number', 'tax_code'], 'safe'],
            [['tax_rate', 'taxable_amount', 'tax_amount', 'total_income', 'total_expenses'], 'number'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = TaxRecord::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['tax_period_start' => SORT_DESC]
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'tax_type' => $this->tax_type,
            'payment_status' => $this->payment_status,
            'tax_code' => $this->tax_code,
        ]);

        $query->andFilterWhere(['>=', 'tax_period_start', $this->tax_period_start])
            ->andFilterWhere(['<=', 'tax_period_end', $this->tax_period_end])
            ->andFilterWhere(['>=', 'tax_amount', $this->tax_amount])
            ->andFilterWhere(['like', 'reference_number', $this->reference_number]);

        return $dataProvider;
    }
}
