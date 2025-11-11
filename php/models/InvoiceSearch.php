<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * InvoiceSearch represents the model behind the search form of `app\models\Invoice`.
 */
class InvoiceSearch extends Invoice
{
    public $customerName;
    public $dateRange;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'customer_id', 'payment_term_id', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['invoice_number', 'invoice_date', 'due_date', 'payment_date', 'status', 'notes', 'currency_code', 'customerName', 'dateRange'], 'safe'],
            [['subtotal', 'tax_amount', 'discount', 'total_amount', 'exchange_rate', 'total_amount_lkr'], 'number'],
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
        $query = Invoice::find();
        $query->joinWith(['customer']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC],
                'attributes' => [
                    'id',
                    'invoice_number',
                    'invoice_date',
                    'due_date',
                    'payment_date',
                    'currency_code',
                    'exchange_rate',
                    'total_amount',
                    'total_amount_lkr',
                    'status',
                    'customerName' => [
                        'asc' => ['customer.company_name' => SORT_ASC],
                        'desc' => ['customer.company_name' => SORT_DESC],
                    ],
                ],
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            $query->where('0=1');
            return $dataProvider;
        }

        // Grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'payment_term_id' => $this->payment_term_id,
            'currency_code' => $this->currency_code,
            'subtotal' => $this->subtotal,
            'tax_amount' => $this->tax_amount,
            'discount' => $this->discount,
            'total_amount' => $this->total_amount,
            'exchange_rate' => $this->exchange_rate,
            'total_amount_lkr' => $this->total_amount_lkr,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
        ]);

        // Handle date fields
        if ($this->invoice_date) {
            $query->andFilterWhere(['DATE(invoice_date)' => $this->invoice_date]);
        }
        if ($this->due_date) {
            $query->andFilterWhere(['DATE(due_date)' => $this->due_date]);
        }
        if ($this->payment_date) {
            $query->andFilterWhere(['DATE(payment_date)' => $this->payment_date]);
        }

        $query->andFilterWhere(['like', 'invoice_number', $this->invoice_number])
            ->andFilterWhere(['like', 'notes', $this->notes])
            ->andFilterWhere(['like', 'invoice.status', $this->status])
            ->andFilterWhere(['like', 'customer.company_name', $this->customerName]);

        return $dataProvider;
    }

    /**
     * Get total amounts for current search results
     * @return array
     */
    public function getTotals()
    {
        $query = clone $this->search(Yii::$app->request->queryParams)->query;

        $invoiceTable = Invoice::tableName();

        return $query->select([
            'total_invoices' => 'COUNT(*)',
            'total_amount' => 'SUM('.$invoiceTable.'.total_amount)',
            'total_amount_lkr' => 'SUM('.$invoiceTable.'.total_amount_lkr)',
            'total_tax' => 'SUM(tax_amount)',
            'total_outstanding' => 'SUM(CASE WHEN '.$invoiceTable.'.status = "pending" OR '.$invoiceTable.'.status = "overdue" THEN total_amount ELSE 0 END)',
            'total_outstanding_lkr' => 'SUM(CASE WHEN '.$invoiceTable.'.status = "pending" OR '.$invoiceTable.'.status = "overdue" THEN total_amount_lkr ELSE 0 END)',
        ])->asArray()->one();
    }
}
