<?php

namespace app\components;

use app\models\Expense;
use app\models\ExpenseSuggestion;
use Yii;
use yii\db\Query;

/**
 * Service to analyze expense patterns and generate suggestions for missing expenses
 */
class ExpenseHealthCheckService
{
    /**
     * Minimum number of consecutive months to establish a pattern
     */
    const MIN_PATTERN_MONTHS = 2;

    /**
     * Maximum months to look back for pattern detection
     */
    const LOOKBACK_MONTHS = 6;

    /**
     * Generate expense suggestions for the current month based on historical patterns
     *
     * @param string|null $targetMonth YYYY-MM-DD format (first day of month). If null, uses current month.
     * @return array ['created' => count, 'skipped' => count, 'errors' => array]
     */
    public function generateSuggestionsForMonth($targetMonth = null)
    {
        $result = [
            'created' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        try {
            // Determine target month (first day of the month)
            if ($targetMonth === null) {
                $targetDate = new \DateTime('first day of this month');
            } else {
                $targetDate = new \DateTime($targetMonth);
                $targetDate->modify('first day of this month');
            }
            $targetMonthStr = $targetDate->format('Y-m-d');

            // Don't generate suggestions for future months
            $currentMonth = new \DateTime('first day of this month');
            if ($targetDate > $currentMonth) {
                Yii::info("Skipping suggestions for future month: {$targetMonthStr}", __METHOD__);
                return $result;
            }

            Yii::info("Generating expense suggestions for month: {$targetMonthStr}", __METHOD__);

            // Find patterns from historical data
            $patterns = $this->detectExpensePatterns($targetDate);

            Yii::info("Detected " . count($patterns) . " expense patterns", __METHOD__);

            foreach ($patterns as $pattern) {
                try {
                    // Check if expense already exists for this month/category/vendor
                    $expenseExists = Expense::find()
                        ->where([
                            'expense_category_id' => $pattern['expense_category_id'],
                            'vendor_id' => $pattern['vendor_id'],
                        ])
                        ->andWhere(['>=', 'expense_date', $targetMonthStr])
                        ->andWhere(['<=', 'expense_date', $targetDate->format('Y-m-t')])
                        ->exists();

                    if ($expenseExists) {
                        $result['skipped']++;
                        Yii::info("Expense already exists for category {$pattern['expense_category_id']}, vendor {$pattern['vendor_id']}", __METHOD__);
                        continue;
                    }

                    // Check if suggestion already exists
                    $existingSuggestion = ExpenseSuggestion::find()
                        ->where([
                            'expense_category_id' => $pattern['expense_category_id'],
                            'vendor_id' => $pattern['vendor_id'],
                            'suggested_month' => $targetMonthStr,
                        ])
                        ->one();

                    if ($existingSuggestion) {
                        // Update existing suggestion if it's still pending
                        if ($existingSuggestion->status === ExpenseSuggestion::STATUS_PENDING) {
                            $existingSuggestion->pattern_months = json_encode($pattern['pattern_months']);
                            $existingSuggestion->avg_amount_lkr = $pattern['avg_amount'];
                            $existingSuggestion->last_expense_id = $pattern['last_expense_id'];
                            $existingSuggestion->generated_at = time();
                            $existingSuggestion->save(false);
                        }
                        $result['skipped']++;
                        continue;
                    }

                    // Check if permanently ignored
                    $permanentlyIgnored = ExpenseSuggestion::find()
                        ->where([
                            'expense_category_id' => $pattern['expense_category_id'],
                            'vendor_id' => $pattern['vendor_id'],
                            'status' => ExpenseSuggestion::STATUS_IGNORED_PERMANENT,
                        ])
                        ->exists();

                    if ($permanentlyIgnored) {
                        $result['skipped']++;
                        Yii::info("Pattern permanently ignored for category {$pattern['expense_category_id']}, vendor {$pattern['vendor_id']}", __METHOD__);
                        continue;
                    }

                    // Create new suggestion
                    $suggestion = new ExpenseSuggestion([
                        'expense_category_id' => $pattern['expense_category_id'],
                        'vendor_id' => $pattern['vendor_id'],
                        'suggested_month' => $targetMonthStr,
                        'pattern_months' => json_encode($pattern['pattern_months']),
                        'avg_amount_lkr' => $pattern['avg_amount'],
                        'last_expense_id' => $pattern['last_expense_id'],
                        'status' => ExpenseSuggestion::STATUS_PENDING,
                        'generated_at' => time(),
                    ]);

                    if ($suggestion->save()) {
                        $result['created']++;
                        Yii::info("Created suggestion for category {$pattern['expense_category_id']}, vendor {$pattern['vendor_id']}", __METHOD__);
                    } else {
                        $result['errors'][] = "Failed to save suggestion: " . json_encode($suggestion->errors);
                    }
                } catch (\Exception $e) {
                    $result['errors'][] = $e->getMessage();
                    Yii::error("Error processing pattern: " . $e->getMessage(), __METHOD__);
                }
            }
        } catch (\Exception $e) {
            $result['errors'][] = $e->getMessage();
            Yii::error("Error generating suggestions: " . $e->getMessage(), __METHOD__);
        }

        return $result;
    }

    /**
     * Detect expense patterns from historical data
     *
     * @param \DateTime $targetDate The date for which to generate suggestions
     * @return array Array of patterns with category_id, vendor_id, pattern_months, avg_amount
     */
    protected function detectExpensePatterns(\DateTime $targetDate)
    {
        $patterns = [];

        // Calculate date range for analysis (look back N months, excluding payroll expenses)
        $endDate = clone $targetDate;
        $endDate->modify('-1 day'); // End of previous month

        $startDate = clone $targetDate;
        $startDate->modify('-' . self::LOOKBACK_MONTHS . ' months');

        // Query to find recurring expense patterns
        // Group by category and vendor, find those with expenses in 2+ consecutive months
        $query = new Query();
        $expenseData = $query
            ->select([
                'expense_category_id',
                'vendor_id',
                'DATE_FORMAT(expense_date, \'%Y-%m-01\') as expense_month',
                'COUNT(*) as expense_count',
                'AVG(COALESCE(amount_lkr, amount)) as avg_amount',
                'MAX(id) as last_expense_id',
            ])
            ->from(Expense::tableName())
            ->where(['>=', 'expense_date', $startDate->format('Y-m-d')])
            ->andWhere(['<=', 'expense_date', $endDate->format('Y-m-d')])
            ->andWhere(['IS NOT', 'vendor_id', null]) // Exclude payroll (null vendor)
            ->groupBy(['expense_category_id', 'vendor_id', 'expense_month'])
            ->orderBy(['expense_category_id' => SORT_ASC, 'vendor_id' => SORT_ASC, 'expense_month' => SORT_ASC])
            ->all();

        // Group by category and vendor
        $groupedData = [];
        foreach ($expenseData as $row) {
            $key = $row['expense_category_id'] . '_' . $row['vendor_id'];
            if (!isset($groupedData[$key])) {
                $groupedData[$key] = [
                    'expense_category_id' => $row['expense_category_id'],
                    'vendor_id' => $row['vendor_id'],
                    'months' => [],
                ];
            }
            $groupedData[$key]['months'][] = [
                'month' => $row['expense_month'],
                'count' => $row['expense_count'],
                'avg_amount' => $row['avg_amount'],
                'last_expense_id' => $row['last_expense_id'],
            ];
        }

        // Analyze each group for patterns
        foreach ($groupedData as $group) {
            if (count($group['months']) < self::MIN_PATTERN_MONTHS) {
                continue; // Not enough data to establish a pattern
            }

            // Check for consecutive months or near-consecutive pattern
            $consecutiveCount = $this->countConsecutiveMonths($group['months']);

            if ($consecutiveCount >= self::MIN_PATTERN_MONTHS) {
                // Calculate average amount across all pattern months
                $totalAmount = 0;
                $lastExpenseId = null;
                $patternMonths = [];

                foreach ($group['months'] as $monthData) {
                    $totalAmount += $monthData['avg_amount'];
                    $patternMonths[] = $monthData['month'];
                    if ($lastExpenseId === null || $monthData['last_expense_id'] > $lastExpenseId) {
                        $lastExpenseId = $monthData['last_expense_id'];
                    }
                }

                $patterns[] = [
                    'expense_category_id' => $group['expense_category_id'],
                    'vendor_id' => $group['vendor_id'],
                    'pattern_months' => $patternMonths,
                    'avg_amount' => round($totalAmount / count($group['months']), 2),
                    'last_expense_id' => $lastExpenseId,
                    'consecutive_count' => $consecutiveCount,
                ];
            }
        }

        return $patterns;
    }

    /**
     * Count consecutive or near-consecutive months in expense pattern
     * Allows for 1 month gap
     *
     * @param array $months Array of month data
     * @return int Number of consecutive months
     */
    protected function countConsecutiveMonths($months)
    {
        if (empty($months)) {
            return 0;
        }

        $monthDates = array_map(function($m) {
            return new \DateTime($m['month']);
        }, $months);

        sort($monthDates);

        $maxConsecutive = 1;
        $currentConsecutive = 1;
        $gapAllowed = 1; // Allow one month gap

        for ($i = 1; $i < count($monthDates); $i++) {
            $prevDate = clone $monthDates[$i - 1];
            $prevDate->modify('+1 month');

            $currentDate = $monthDates[$i];
            $diff = $prevDate->diff($currentDate);
            $monthsDiff = ($diff->y * 12) + $diff->m;

            if ($monthsDiff === 0) {
                // Consecutive month
                $currentConsecutive++;
            } elseif ($monthsDiff === 1 && $gapAllowed > 0) {
                // One month gap, allowed
                $currentConsecutive++;
                $gapAllowed--;
            } else {
                // Reset
                $maxConsecutive = max($maxConsecutive, $currentConsecutive);
                $currentConsecutive = 1;
                $gapAllowed = 1;
            }
        }

        return max($maxConsecutive, $currentConsecutive);
    }

    /**
     * Reset ignored status for a category/vendor combination when a new expense is added
     * This removes permanent ignore status when user adds an expense for that pattern
     *
     * @param int $expenseCategoryId
     * @param int $vendorId
     * @return int Number of suggestions reset
     */
    public function resetIgnoredSuggestions($expenseCategoryId, $vendorId)
    {
        try {
            $count = ExpenseSuggestion::updateAll(
                ['status' => ExpenseSuggestion::STATUS_PENDING, 'ignored_reason' => null],
                [
                    'expense_category_id' => $expenseCategoryId,
                    'vendor_id' => $vendorId,
                    'status' => ExpenseSuggestion::STATUS_IGNORED_PERMANENT,
                ]
            );

            if ($count > 0) {
                Yii::info("Reset {$count} permanently ignored suggestions for category {$expenseCategoryId}, vendor {$vendorId}", __METHOD__);
            }

            return $count;
        } catch (\Exception $e) {
            Yii::error("Error resetting ignored suggestions: " . $e->getMessage(), __METHOD__);
            return 0;
        }
    }

    /**
     * Get pending suggestions count
     *
     * @return int
     */
    public function getPendingSuggestionsCount()
    {
        try {
            return ExpenseSuggestion::find()
                ->where(['status' => ExpenseSuggestion::STATUS_PENDING])
                ->count();
        } catch (\Exception $e) {
            Yii::error("Error getting pending suggestions count: " . $e->getMessage(), __METHOD__);
            return 0;
        }
    }

    /**
     * Generate suggestions for all past months up to current month
     * This scans the entire history and suggests missing expenses for any month
     *
     * @param int $lookbackMonths How many months back to check (default 6)
     * @return array ['created' => count, 'skipped' => count, 'errors' => array, 'months_scanned' => array]
     */
    public function generateSuggestionsForAllPastMonths($lookbackMonths = 6)
    {
        $result = [
            'created' => 0,
            'skipped' => 0,
            'errors' => [],
            'months_scanned' => [],
        ];

        try {
            $currentMonth = new \DateTime('first day of this month');

            // Scan from lookback months ago to current month
            for ($i = 0; $i <= $lookbackMonths; $i++) {
                $targetMonth = clone $currentMonth;
                $targetMonth->modify("-{$i} months");
                $targetMonthStr = $targetMonth->format('Y-m-d');

                $result['months_scanned'][] = $targetMonth->format('Y-m');

                $monthResult = $this->generateSuggestionsForMonth($targetMonthStr);
                $result['created'] += $monthResult['created'];
                $result['skipped'] += $monthResult['skipped'];
                $result['errors'] = array_merge($result['errors'], $monthResult['errors']);
            }

            Yii::info("Scanned " . count($result['months_scanned']) . " months, created {$result['created']} suggestions", __METHOD__);
        } catch (\Exception $e) {
            $result['errors'][] = $e->getMessage();
            Yii::error("Error scanning all past months: " . $e->getMessage(), __METHOD__);
        }

        return $result;
    }

    /**
     * Clean up old temporary ignores (convert to pending after 2 months)
     *
     * @return int Number of suggestions reset
     */
    public function cleanupTemporaryIgnores()
    {
        try {
            $twoMonthsAgo = strtotime('-2 months');

            $count = ExpenseSuggestion::updateAll(
                ['status' => ExpenseSuggestion::STATUS_PENDING, 'ignored_reason' => null],
                [
                    'and',
                    ['status' => ExpenseSuggestion::STATUS_IGNORED_TEMPORARY],
                    ['<', 'actioned_at', $twoMonthsAgo]
                ]
            );

            if ($count > 0) {
                Yii::info("Reset {$count} temporary ignored suggestions older than 2 months", __METHOD__);
            }

            return $count;
        } catch (\Exception $e) {
            Yii::error("Error cleaning up temporary ignores: " . $e->getMessage(), __METHOD__);
            return 0;
        }
    }
}

