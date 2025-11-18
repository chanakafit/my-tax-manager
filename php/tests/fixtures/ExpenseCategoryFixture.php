<?php

namespace tests\fixtures;

use yii\test\ActiveFixture;

/**
 * ExpenseCategory fixture
 */
class ExpenseCategoryFixture extends ActiveFixture
{
    public $modelClass = 'app\models\ExpenseCategory';
    public $dataFile = '@tests/_data/expense_category.php';
}

