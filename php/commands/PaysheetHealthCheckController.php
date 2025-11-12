<?php

namespace app\commands;

use app\components\PaysheetHealthCheckService;
use yii\console\Controller;
use yii\console\ExitCode;
use Yii;

/**
 * Paysheet Health Check Console Controller
 * Run via cron to generate monthly paysheet suggestions
 */
class PaysheetHealthCheckController extends Controller
{
    /**
     * Generate paysheet suggestions for the current month only
     * This command should be run on the 1st of each month via cron
     * Note: Will NOT generate suggestions for future months
     *
     * Example cron entry (runs at 2 AM on the 1st of each month):
     * 0 2 1 * * cd /path/to/php && php yii paysheet-health-check/generate
     *
     * @return int Exit code
     */
    public function actionGenerate()
    {
        $this->stdout("Starting paysheet health check generation for current month...\n");

        $service = new PaysheetHealthCheckService();

        // Generate suggestions for current month only
        $result = $service->generateSuggestionsForMonth();

        $this->stdout("Paysheet health check completed:\n");
        $this->stdout("  - Created: {$result['created']} suggestions\n");
        $this->stdout("  - Skipped: {$result['skipped']} (already exist or have paysheet)\n");

        if (!empty($result['errors'])) {
            $this->stderr("  - Errors encountered:\n");
            foreach ($result['errors'] as $error) {
                $this->stderr("    * {$error}\n");
            }
            return ExitCode::UNSPECIFIED_ERROR;
        }

        // Also cleanup old rejected suggestions
        $cleanedUp = $service->cleanupRejectedSuggestions();
        if ($cleanedUp > 0) {
            $this->stdout("  - Cleaned up {$cleanedUp} old rejected suggestions\n");
        }

        $this->stdout("Done!\n");
        return ExitCode::OK;
    }

    /**
     * Generate paysheet suggestions for all past months (including current)
     * Scans historical data and suggests missing paysheets for any past month
     * Note: Will NOT generate suggestions for future months
     *
     * @param int $months Number of months back to scan (default 6)
     * @return int Exit code
     */
    public function actionGenerateAll($months = 6)
    {
        $this->stdout("Starting paysheet health check generation for all past months...\n");
        $this->stdout("Scanning last {$months} months...\n");

        $service = new PaysheetHealthCheckService();

        // Generate suggestions for all past months
        $result = $service->generateSuggestionsForAllPastMonths($months);

        $this->stdout("Paysheet health check completed:\n");
        $this->stdout("  - Months scanned: " . implode(', ', $result['months_scanned']) . "\n");
        $this->stdout("  - Created: {$result['created']} suggestions\n");
        $this->stdout("  - Skipped: {$result['skipped']} (already exist or have paysheet)\n");

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
     * Generate paysheet suggestions for a specific month (current or past only)
     * Note: Will NOT generate suggestions for future months
     *
     * @param string $month Month in YYYY-MM format or YYYY-MM-DD format
     * @return int Exit code
     */
    public function actionGenerateForMonth($month)
    {
        $this->stdout("Generating paysheet health check for month: {$month}...\n");

        try {
            // Normalize month format
            if (preg_match('/^\d{4}-\d{2}$/', $month)) {
                $month = $month . '-01';
            }

            $targetDate = new \DateTime($month);
            $targetDate->modify('first day of this month');

            $service = new PaysheetHealthCheckService();
            $result = $service->generateSuggestionsForMonth($targetDate->format('Y-m-d'));

            $this->stdout("Paysheet health check completed for {$targetDate->format('F Y')}:\n");
            $this->stdout("  - Created: {$result['created']} suggestions\n");
            $this->stdout("  - Skipped: {$result['skipped']} (already exist or have paysheet)\n");

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
     * Get count of pending paysheet suggestions
     *
     * @return int Exit code
     */
    public function actionCount()
    {
        $service = new PaysheetHealthCheckService();
        $count = $service->getPendingSuggestionsCount();

        $this->stdout("Pending paysheet suggestions: {$count}\n");
        return ExitCode::OK;
    }

    /**
     * Cleanup old rejected suggestions
     *
     * @param int $days Delete rejected suggestions older than this many days (default 90)
     * @return int Exit code
     */
    public function actionCleanup($days = 90)
    {
        $this->stdout("Cleaning up rejected paysheet suggestions older than {$days} days...\n");

        $service = new PaysheetHealthCheckService();
        $deleted = $service->cleanupRejectedSuggestions($days);

        $this->stdout("Deleted {$deleted} old rejected suggestions.\n");
        $this->stdout("Done!\n");
        return ExitCode::OK;
    }
}

