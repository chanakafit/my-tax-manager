<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Paysheet;

/**
 * PaysheetSearch represents the model behind the search form of `app\models\Paysheet`.
 */
class PaysheetSearch extends Paysheet
{
    public $year;
    public $month;
    public $employee_name;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'employee_id', 'created_at', 'updated_at', 'created_by', 'updated_by', 'year', 'month'], 'integer'],
            [['pay_period_start', 'pay_period_end', 'payment_date', 'payment_method', 'payment_reference', 'status', 'notes', 'employee_name'], 'safe'],
            [['basic_salary', 'allowances', 'deductions', 'tax_amount', 'net_salary'], 'number'],
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
        $query = Paysheet::find();
        $query->joinWith(['employee']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'pay_period_start' => SORT_DESC,
                ],
                'attributes' => [
                    'employee_name' => [
                        'asc' => ['employee.first_name' => SORT_ASC, 'employee.last_name' => SORT_ASC],
                        'desc' => ['employee.first_name' => SORT_DESC, 'employee.last_name' => SORT_DESC],
                    ],
                    'pay_period_start',
                    'pay_period_end',
                    'payment_date',
                    'basic_salary',
                    'net_salary',
                    'status',
                ],
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'basic_salary' => $this->basic_salary,
            'allowances' => $this->allowances,
            'deductions' => $this->deductions,
            'tax_amount' => $this->tax_amount,
            'net_salary' => $this->net_salary,
            'status' => $this->status,
        ]);

        // Handle date-based filtering
        if ($this->year) {
            if ($this->month) {
                // Filter for specific month
                $startDate = date('Y-m-01', strtotime("{$this->year}-{$this->month}-01"));
                $endDate = date('Y-m-t', strtotime($startDate));
                $query->andWhere(['between', 'pay_period_start', $startDate, $endDate]);
            } else {
                // Filter for entire year
                $startDate = "{$this->year}-01-01";
                $endDate = "{$this->year}-12-31";
                $query->andWhere(['between', 'pay_period_start', $startDate, $endDate]);
            }
        }

        // Filter by employee name
        if ($this->employee_name) {
            $query->andWhere(['or',
                ['like', 'employee.first_name', $this->employee_name],
                ['like', 'employee.last_name', $this->employee_name]
            ]);
        }

        $query->andFilterWhere(['like', 'payment_method', $this->payment_method])
            ->andFilterWhere(['like', 'payment_reference', $this->payment_reference])
            ->andFilterWhere(['like', 'notes', $this->notes]);

        return $dataProvider;
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'year' => 'Year',
            'month' => 'Month',
            'employee_name' => 'Employee Name',
        ]);
    }
}
