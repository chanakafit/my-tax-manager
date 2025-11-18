<?php

namespace tests\fixtures;

use yii\test\ActiveFixture;

/**
 * Expense fixture
 */
class ExpenseFixture extends ActiveFixture
{
    public $modelClass = 'app\models\Expense';
    public $dataFile = '@tests/_data/expense.php';

    public $depends = [
        'tests\fixtures\ExpenseCategoryFixture',
        'tests\fixtures\VendorFixture',
    ];
}

