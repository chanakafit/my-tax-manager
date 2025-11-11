<?php

namespace app\widgets;

class BHtml extends \kartik\helpers\Html
{
    public static function switcher($checked = true, $url = '', $options = []): string
    {
        if ($checked) {
            return self::a('<mwc-switch selected></mwc-switch>', $url, $options);
        }
        return self::a('<mwc-switch></mwc-switch>', $url, $options);
    }
}