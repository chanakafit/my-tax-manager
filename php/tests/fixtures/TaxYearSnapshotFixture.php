<?php

namespace tests\fixtures;

use yii\test\ActiveFixture;

class TaxYearSnapshotFixture extends ActiveFixture
{
    public $modelClass = 'app\models\TaxYearSnapshot';
    public $dataFile = '@tests/_data/tax_year_snapshot.php';
}

