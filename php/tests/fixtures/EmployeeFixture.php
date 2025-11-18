<?php

namespace tests\fixtures;

use yii\test\ActiveFixture;

/**
 * Employee fixture
 */
class EmployeeFixture extends ActiveFixture
{
    public $modelClass = 'app\models\Employee';
    public $dataFile = '@tests/_data/employee.php';
}

