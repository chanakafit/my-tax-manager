<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Expense;

/**
 * ExpenseSearch represents the model behind the search form of `app\models\Expense`.
 */
class ExpenseSearch extends Expense
{
    /**
     * @var string Virtual attribute for searching by vendor name
     */
    public $vendor_name;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'expense_category_id', 'vendor_id', 'is_recurring', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['expense_date', 'title', 'description', 'receipt_number', 'payment_method', 'status', 'vendor_name', 'recurring_interval', 'next_recurring_date'], 'safe'],
            [['amount', 'tax_amount'], 'number'],
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
        $query = Expense::find();
        $query->joinWith(['vendor']);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        // Enable sorting by vendor name
        $dataProvider->sort->attributes['vendor_id'] = [
            'asc' => ['{{%vendor}}.name' => SORT_ASC],
            'desc' => ['{{%vendor}}.name' => SORT_DESC],
        ];

        $this->load($params, $formName);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            '{{%expense}}.id' => $this->id,
            '{{%expense}}.expense_category_id' => $this->expense_category_id,
            '{{%expense}}.vendor_id' => $this->vendor_id,
            '{{%expense}}.expense_date' => $this->expense_date,
            '{{%expense}}.amount' => $this->amount,
            '{{%expense}}.tax_amount' => $this->tax_amount,
            '{{%expense}}.is_recurring' => $this->is_recurring,
            '{{%expense}}.next_recurring_date' => $this->next_recurring_date,
            '{{%expense}}.created_at' => $this->created_at,
            '{{%expense}}.updated_at' => $this->updated_at,
            '{{%expense}}.created_by' => $this->created_by,
            '{{%expense}}.updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', '{{%expense}}.title', $this->title])
            ->andFilterWhere(['like', '{{%expense}}.description', $this->description])
            ->andFilterWhere(['like', '{{%expense}}.receipt_number', $this->receipt_number])
            ->andFilterWhere(['like', '{{%expense}}.payment_method', $this->payment_method])
            ->andFilterWhere(['like', '{{%expense}}.status', $this->status])
            ->andFilterWhere(['like', '{{%vendor}}.name', $this->vendor_name])
            ->andFilterWhere(['like', '{{%expense}}.recurring_interval', $this->recurring_interval]);

        return $dataProvider;
    }
}
