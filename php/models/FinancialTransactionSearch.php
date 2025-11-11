<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\FinancialTransaction;

/**
 * FinancialTransactionSearch represents the model behind the search form of `app\models\FinancialTransaction`.
 */
class FinancialTransactionSearch extends FinancialTransaction
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'bank_account_id', 'related_invoice_id', 'related_expense_id', 'related_paysheet_id', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['transaction_date', 'transaction_type', 'reference_number', 'description', 'category', 'payment_method', 'status'], 'safe'],
            [['amount'], 'number'],
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
     * @param string|null $formName Form name to be used into `->load()` method.
     *
     * @return ActiveDataProvider
     */
    public function search($params, $formName = null)
    {
        $query = FinancialTransaction::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'bank_account_id' => $this->bank_account_id,
            'transaction_date' => $this->transaction_date,
            'amount' => $this->amount,
            'related_invoice_id' => $this->related_invoice_id,
            'related_expense_id' => $this->related_expense_id,
            'related_paysheet_id' => $this->related_paysheet_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'transaction_type', $this->transaction_type])
            ->andFilterWhere(['like', 'reference_number', $this->reference_number])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'category', $this->category])
            ->andFilterWhere(['like', 'payment_method', $this->payment_method])
            ->andFilterWhere(['like', 'status', $this->status]);

        return $dataProvider;
    }
}
