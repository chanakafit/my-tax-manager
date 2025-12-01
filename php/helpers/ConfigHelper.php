<?php

namespace app\helpers;

use app\models\SystemConfig;
use Yii;

/**
 * Helper class to maintain backward compatibility with old params.php usage
 * This class provides static methods to access configuration values
 */
class ConfigHelper
{
    /**
     * Get configuration value
     * First checks params.php, then falls back to database
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        // Prefer database-backed configuration (SystemConfig) first.
        // If not present in DB (null), fall back to params.php.
        // Note: SystemConfig::get returns $default when not found, so we request null
        // to detect absence distinctly.
        $dbValue = SystemConfig::get($key, null);
        if ($dbValue !== null) {
            return $dbValue;
        }

        // Fallback to params.php or provided default
        return Yii::$app->params[$key] ?? $default;
    }

    /**
     * Get business name
     *
     * @return string
     */
    public static function getBusinessName(): string
    {
        return SystemConfig::get('businessName', 'CHANAKA IT SERVICES');
    }

    /**
     * Get business address
     *
     * @return array
     */
    public static function getBusinessAddress(): array
    {
        return SystemConfig::getBusinessAddress();
    }

    /**
     * Get banking details
     *
     * @return array
     */
    public static function getBankingDetails(): array
    {
        return SystemConfig::getBankingDetails();
    }

    /**
     * Get admin email
     *
     * @return string
     */
    public static function getAdminEmail(): string
    {
        return SystemConfig::get('adminEmail', 'admin@example.com');
    }

    /**
     * Get sender email
     *
     * @return string
     */
    public static function getSenderEmail(): string
    {
        return SystemConfig::get('senderEmail', 'noreply@example.com');
    }

    /**
     * Get sender name
     *
     * @return string
     */
    public static function getSenderName(): string
    {
        return SystemConfig::get('senderName', 'System');
    }

    /**
     * Get payment methods
     *
     * @return array
     */
    public static function getPaymentMethods(): array
    {
        return SystemConfig::get('paymentMethods', [
            'cash' => 'Cash',
            'bank_transfer' => 'Bank Transfer',
        ]);
    }

    /**
     * Get invoice statuses
     *
     * @return array
     */
    public static function getInvoiceStatuses(): array
    {
        return SystemConfig::get('invoiceStatuses', [
            'pending' => 'Pending',
            'paid' => 'Paid',
        ]);
    }

    /**
     * Get departments
     *
     * @return array
     */
    public static function getDepartments(): array
    {
        return SystemConfig::get('departments', []);
    }

    /**
     * Get currencies
     *
     * @return array
     */
    public static function getCurrencies(): array
    {
        return SystemConfig::get('currencies', [
            'LKR' => 'Sri Lankan Rupee',
            'USD' => 'US Dollar',
        ]);
    }

    /**
     * Get bank names
     *
     * @return array
     */
    public static function getBankNames(): array
    {
        return SystemConfig::get('bankNames', []);
    }

    /**
     * Get currency symbol
     *
     * @return string
     */
    public static function getCurrencySymbol(): string
    {
        return SystemConfig::get('currencySymbol', 'Rs. ');
    }

    /**
     * Get payroll expense category ID
     *
     * @return int
     */
    public static function getPayrollExpenseCategoryId(): int
    {
        return (int)SystemConfig::get('payrollExpenseCategoryId', 4);
    }

    /**
     * Get default bank account ID
     *
     * @return int
     */
    public static function getDefaultBankAccountId(): int
    {
        return (int)SystemConfig::get('defaultBankAccountId', 1);
    }

    /**
     * Get invoice number start
     *
     * @return int
     */
    public static function getInvoiceNumberStart(): int
    {
        return (int)SystemConfig::get('invoiceNumberStart', 1);
    }

    /**
     * Get invoice number format
     *
     * @return string
     */
    public static function getInvoiceNumberFormat(): string
    {
        return SystemConfig::get('invoiceNumberFormat', 'INV-%06d');
    }

    /**
     * Get signature details as array
     *
     * @return array
     */
    public static function getSignatureDetails(): array
    {
        return [
            'image' => SystemConfig::get('signatureImage', ''),
            'name' => SystemConfig::get('signatureName', ''),
            'title' => SystemConfig::get('signatureTitle', ''),
        ];
    }

    /**
     * Get signature image path
     *
     * @param string $default
     * @return string
     */
    public static function getSignatureImage(string $default = ''): string
    {
        $value = SystemConfig::get('signatureImage', $default);
        if (empty($value)) {
            return '';
        }

        // Normalize path: ensure it starts with a slash unless it's a full URL
        if (strpos($value, '/') !== 0 && stripos($value, 'http') !== 0) {
            $value = '/' . $value;
        }

        return $value;
    }

    /**
     * Get signature name
     *
     * @param string $default
     * @return string
     */
    public static function getSignatureName(string $default = ''): string
    {
        return SystemConfig::get('signatureName', $default);
    }

    /**
     * Get signature title
     *
     * @param string $default
     * @return string
     */
    public static function getSignatureTitle(string $default = ''): string
    {
        return SystemConfig::get('signatureTitle', $default);
    }
}
