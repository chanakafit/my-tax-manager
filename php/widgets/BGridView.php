<?php

namespace app\widgets;

use yii\helpers\ArrayHelper;

class BGridView extends \kartik\grid\GridView
{
    public $persistResize = true;

    public $hover = true;

    public $pjax = true;

    public $toggleDataContainer = ['class' => 'btn-group mr-2'];

    public $export = [
        'fontAwesome' => true,
        'label'       => 'Export data'
    ];

    public $condensed = true;

    public $showPageSummary = false;

    /**
     * @var string the heading of the grid view. Note that this is not HTML-encoded.
     */
    public string $heading = '';

    public $panel = [
        'type' => BGridView::TYPE_PRIMARY,
    ];

    public $toggleDataOptions = ['minCount' => 10];

    public $exportConfig = false;

    public $pager = [
        'nextPageLabel'    => '<i class="fa-solid fa-angle-right fa-2xs"></i>',
        'prevPageLabel'    => '<i class="fa-solid fa-angle-left fa-2xs"></i>',
        'prevPageCssClass' => 'page-item',
        'nextPageCssClass' => 'page-item',
        'maxButtonCount'   => 10,
        'firstPageLabel'   => '<i class="fa-solid fa-angles-left fa-2xs"></i>',
        'lastPageLabel'    => '<i class="fa-solid fa-angles-right fa-2xs"></i>',
    ];

    public function __construct($config = [])
    {
        parent::__construct($config);
        if (!isset($this->panel['heading'])) {
            $this->panel['heading'] = $this->heading ?? '';
        }

        $this->rowOptions = ArrayHelper::merge($this->rowOptions, function ($model, $key, $index, $grid) {
            return ['id'      => $model['id'],
                    'onclick' => 'location.href="' . \yii\helpers\Url::to(['view', 'id' => $model['id']]) . '"'
            ];
        });
    }

}