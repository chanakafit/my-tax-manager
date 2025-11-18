<?php

namespace tests\fixtures;

use yii\test\ActiveFixture;

/**
 * EmployeeSalaryAdvance fixture
 */
class EmployeeSalaryAdvanceFixture extends ActiveFixture
{
    public $modelClass = 'app\models\EmployeeSalaryAdvance';
    public $dataFile = '@tests/_data/employee_salary_advance.php';

    public $depends = [
        'tests\fixtures\EmployeeFixture',
    ];
}

