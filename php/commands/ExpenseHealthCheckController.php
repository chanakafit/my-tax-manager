<?php

namespace app\commands;

use app\components\ExpenseHealthCheckService;
use yii\console\Controller;
use yii\console\ExitCode;
use Yii;

/**
 * Expense Health Check Console Controller
 * Run via cron to generate monthly expense suggestions
 */
class ExpenseHealthCheckController extends Controller
{
    /**
     * Generate expense suggestions for the current month only
     * This command should be run on the 1st of each month via cron
     * Note: Will NOT generate suggestions for future months
     *
     * Example cron entry (runs at 1 AM on the 1st of each month):
     * 0 1 1 * * cd /path/to/php && php yii expense-health-check/generate
     *
     * @return int Exit code
     */
    public function actionGenerate()
    {
        $this->stdout("Starting expense health check generation for current month...\n");

        $service = new ExpenseHealthCheckService();

        // Generate suggestions for current month only
        $result = $service->generateSuggestionsForMonth();

        $this->stdout("Expense health check completed:\n");
        $this->stdout("  - Created: {$result['created']} suggestions\n");
        $this->stdout("  - Skipped: {$result['skipped']} (already exist or ignored)\n");

        if (!empty($result['errors'])) {
            $this->stderr("  - Errors encountered:\n");
            foreach ($result['errors'] as $error) {
                $this->stderr("    * {$error}\n");
            }
            return ExitCode::UNSPECIFIED_ERROR;
        }

        // Also cleanup old temporary ignores
        $cleanedUp = $service->cleanupTemporaryIgnores();
        if ($cleanedUp > 0) {
            $this->stdout("  - Reset {$cleanedUp} old temporary ignores\n");
        }

        $this->stdout("Done!\n");
        return ExitCode::OK;
    }

    /**
     * Generate expense suggestions for all past months (including current)
     * Scans historical data and suggests missing expenses for any past month where pattern exists
     * Note: Will NOT generate suggestions for future months
     *
     * @param int $months Number of months back to scan (default 6)
     * @return int Exit code
     */
    public function actionGenerateAll($months = 6)
    {
        $this->stdout("Starting expense health check generation for all past months...\n");
        $this->stdout("Scanning last {$months} months...\n");

        $service = new ExpenseHealthCheckService();

        // Generate suggestions for all past months
        $result = $service->generateSuggestionsForAllPastMonths($months);

        $this->stdout("Expense health check completed:\n");
        $this->stdout("  - Months scanned: " . implode(', ', $result['months_scanned']) . "\n");
        $this->stdout("  - Created: {$result['created']} suggestions\n");
        $this->stdout("  - Skipped: {$result['skipped']} (already exist or ignored)\n");

        if (!empty($result['errors'])) {
            $this->stderr("  - Errors encountered:\n");
            foreach ($result['errors'] as $error) {
                $this->stderr("    * {$error}\n");
            }
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->stdout("Done!\n");
        return ExitCode::OK;
    }

    /**
     * Generate expense suggestions for a specific month (current or past only)
     * Note: Will NOT generate suggestions for future months
     *
     * @param string $month Month in YYYY-MM format or YYYY-MM-DD format
     * @return int Exit code
     */
    public function actionGenerateForMonth($month)
    {
        $this->stdout("Generating expense health check for month: {$month}...\n");

        try {
            // Validate and parse month
            $date = new \DateTime($month);
            $monthStr = $date->format('Y-m-01');

            // Check if it's a future month
            $currentMonth = new \DateTime('first day of this month');
            if ($date > $currentMonth) {
                $this->stderr("Warning: Cannot generate suggestions for future month ({$monthStr}).\n");
                $this->stderr("Suggestions are only generated for current and past months.\n");
                return ExitCode::OK;
            }

            $service = new ExpenseHealthCheckService();
            $result = $service->generateSuggestionsForMonth($monthStr);

            $this->stdout("Expense health check completed for {$monthStr}:\n");
            $this->stdout("  - Created: {$result['created']} suggestions\n");
            $this->stdout("  - Skipped: {$result['skipped']} (already exist or ignored)\n");

            if (!empty($result['errors'])) {
                $this->stderr("  - Errors encountered:\n");
                foreach ($result['errors'] as $error) {
                    $this->stderr("    * {$error}\n");
                }
                return ExitCode::UNSPECIFIED_ERROR;
            }

            $this->stdout("Done!\n");
            return ExitCode::OK;
        } catch (\Exception $e) {
            $this->stderr("Error: {$e->getMessage()}\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Clean up old temporary ignores
     *
     * @return int Exit code
     */
    public function actionCleanup()
    {
        $this->stdout("Cleaning up old temporary ignores...\n");

        $service = new ExpenseHealthCheckService();
        $count = $service->cleanupTemporaryIgnores();

        $this->stdout("Reset {$count} old temporary ignores\n");
        $this->stdout("Done!\n");

        return ExitCode::OK;
    }

    /**
     * Show pending suggestions count
     *
     * @return int Exit code
     */
    public function actionCount()
    {
        $service = new ExpenseHealthCheckService();
        $count = $service->getPendingSuggestionsCount();

        $this->stdout("Pending expense suggestions: {$count}\n");

        return ExitCode::OK;
    }
}

