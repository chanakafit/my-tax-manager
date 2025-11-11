<?php

namespace app\widgets;


use yii\widgets\ActiveForm;

class BActiveForm extends ActiveForm
{

    public $fieldClass = BActiveField::class;

}