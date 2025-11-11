<?php

namespace app\models\forms;

use yii\base\Model;

class PaysheetGenerateForm extends Model
{
    public $employee_ids;
    public $month;
    public $year;

    public function rules()
    {
        return [
            [['employee_ids', 'year'], 'required'],
            ['month', 'integer', 'min' => 1, 'max' => 12],
            ['year', 'integer', 'min' => 2000],
            ['year', 'validateYear'],
            [['employee_ids'], 'each', 'rule' => ['integer']],
        ];
    }

    public function validateYear($attribute, $params)
    {
        if ($this->year > date('Y')) {
            $this->addError($attribute, 'Cannot generate paysheets for future years.');
        }
    }

    public function attributeLabels()
    {
        return [
            'employee_ids' => 'Employees',
            'month' => 'Month (Optional)',
            'year' => 'Year',
        ];
    }
}
