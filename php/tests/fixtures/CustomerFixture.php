<?php

namespace tests\fixtures;

use yii\test\ActiveFixture;

/**
 * Customer fixture
 */
class CustomerFixture extends ActiveFixture
{
    public $modelClass = 'app\models\Customer';
    public $dataFile = '@tests/_data/customer.php';
}

