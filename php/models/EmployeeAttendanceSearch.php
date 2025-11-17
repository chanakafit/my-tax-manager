<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * EmployeeAttendanceSearch represents the model behind the search form of `app\models\EmployeeAttendance`.
 */
class EmployeeAttendanceSearch extends EmployeeAttendance
{
    public $employee_name;

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['id', 'employee_id', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['attendance_date', 'attendance_type', 'notes', 'employee_name'], 'safe'],
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
        $query = EmployeeAttendance::find()->joinWith(['employee']);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['attendance_date' => SORT_DESC],
            ],
        ]);

        // Enable sorting by employee name
        $dataProvider->sort->attributes['employee_name'] = [
            'asc' => ['employee.first_name' => SORT_ASC, 'employee.last_name' => SORT_ASC],
            'desc' => ['employee.first_name' => SORT_DESC, 'employee.last_name' => SORT_DESC],
        ];

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'attendance_date' => $this->attendance_date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'attendance_type', $this->attendance_type])
            ->andFilterWhere(['like', 'notes', $this->notes]);

        // Filter by employee name
        if ($this->employee_name) {
            $query->andWhere([
                'or',
                ['like', 'employee.first_name', $this->employee_name],
                ['like', 'employee.last_name', $this->employee_name],
            ]);
        }

        return $dataProvider;
    }
}

