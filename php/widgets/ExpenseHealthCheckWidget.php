<?php

namespace app\widgets;

use app\components\ExpenseHealthCheckService;
use app\models\ExpenseSuggestion;
use yii\base\Widget;
use Yii;

/**
 * Widget to display expense health check suggestions on dashboard
 */
class ExpenseHealthCheckWidget extends Widget
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
        $service = new ExpenseHealthCheckService();
        $pendingCount = $service->getPendingSuggestionsCount();

        $suggestions = [];
        if ($this->showDetails && $pendingCount > 0) {
            $suggestions = ExpenseSuggestion::find()
                ->with(['expenseCategory', 'vendor'])
                ->where(['status' => ExpenseSuggestion::STATUS_PENDING])
                ->orderBy(['suggested_month' => SORT_DESC])
                ->limit($this->limit)
                ->all();
        }

        return $this->render('expense-health-check', [
            'pendingCount' => $pendingCount,
            'suggestions' => $suggestions,
            'showDetails' => $this->showDetails,
        ]);
    }
}

