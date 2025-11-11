<?php

namespace app\widgets;

use kartik\detail\DetailView;
use Yii;

class BDetailView extends DetailView
{
    public $condensed = true;
    public $hover = true;
    public $mode = self::MODE_VIEW;
    public $enableEditMode = false;

    public $heading = '';

    public function init()
    {
        parent::init();
        $this->initDefaultOptions();
    }

    public function __construct($config = [])
    {
        parent::__construct($config);
        if (!isset($this->panel['heading'])) {
            $this->panel['heading'] = $this->heading ?? '';
        }
    }

    protected function initDefaultOptions()
    {
        $this->panel['type'] = self::TYPE_INFO;
    }
}