<?php

namespace app\widgets;

use yii\widgets\ActiveField;

class BActiveField extends ActiveField
{
    public $template = "{label}\n{input}\n{hint}\n<span class='text-danger'>{error}</span>";
}