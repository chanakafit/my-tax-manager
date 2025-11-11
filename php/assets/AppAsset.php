<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Main application asset bundle.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.css',
        '//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css',
    ];
    public $js = [
        'https://code.jquery.com/ui/1.13.2/jquery-ui.min.js',
        'js/finance.js',
        [
            'https://kit.fontawesome.com/1f0cdf0f48.js',
            'crossorigin' => 'anonymous'
        ]
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap5\BootstrapAsset',
    ];
}
