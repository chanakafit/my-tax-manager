<?php

namespace tests\fixtures;

use yii\test\ActiveFixture;

/**
 * Invoice fixture
 */
class InvoiceFixture extends ActiveFixture
{
    public $modelClass = 'app\models\Invoice';
    public $dataFile = '@tests/_data/invoice.php';

    public $depends = [
        'tests\fixtures\CustomerFixture',
    ];
}

