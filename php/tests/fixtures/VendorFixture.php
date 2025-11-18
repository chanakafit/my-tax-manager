<?php

namespace tests\fixtures;

use yii\test\ActiveFixture;

/**
 * Vendor fixture
 */
class VendorFixture extends ActiveFixture
{
    public $modelClass = 'app\models\Vendor';
    public $dataFile = '@tests/_data/vendor.php';
}

