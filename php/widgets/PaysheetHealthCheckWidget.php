<?php

namespace app\widgets;

use app\components\PaysheetHealthCheckService;
use app\models\PaysheetSuggestion;
use yii\base\Widget;
use Yii;

/**
 * Widget to display paysheet health check suggestions on dashboard
 */
class PaysheetHealthCheckWidget extends Widget
{
    /**
     * @var int Maximum number of suggestions to show
     */
    public $limit = 5;

    /**
     * @var bool Show full details or summary only
     */
    public $showDetails = true;

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $service = new PaysheetHealthCheckService();
        $pendingCount = $service->getPendingSuggestionsCount();

        $suggestions = [];
        if ($this->showDetails && $pendingCount > 0) {
            $suggestions = PaysheetSuggestion::find()
                ->with(['employee'])
                ->where(['status' => PaysheetSuggestion::STATUS_PENDING])
                ->orderBy(['suggested_month' => SORT_DESC])
                ->limit($this->limit)
                ->all();
        }

        return $this->render('paysheet-health-check', [
            'pendingCount' => $pendingCount,
            'suggestions' => $suggestions,
            'showDetails' => $this->showDetails,
        ]);
    }
}

