<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%system_config}}`.
 * This table stores business configuration that can be updated through the UI
 */
class m251116_000002_create_system_config_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Check if table already exists
        $tableName = '{{%system_config}}';
        $tableSchema = $this->db->getTableSchema($tableName);
        $tableExists = ($tableSchema !== null);

        if (!$tableExists) {
            // Table doesn't exist, create it
            $this->createTable('{{%system_config}}', [
            'id' => $this->primaryKey(),
            'config_key' => $this->string(100)->notNull()->unique(),
            'config_value' => $this->text(),
            'config_type' => $this->string(20)->notNull()->defaultValue('string')
                ->comment('string, integer, boolean, json, array'),
            'category' => $this->string(50)->notNull()->comment('business, banking, system, invoice, etc'),
            'description' => $this->string(255),
            'is_editable' => $this->boolean()->defaultValue(true),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ]);

        // Add indexes
        $this->createIndex(
            'idx-system_config-config_key',
            '{{%system_config}}',
            'config_key',
            true
        );

        $this->createIndex(
            'idx-system_config-category',
            '{{%system_config}}',
            'category'
        );
        } else {
            echo "    > table $tableName already exists ...skipping table creation.\n";
        }

        // Check if data already exists
        $count = $this->db->createCommand("SELECT COUNT(*) FROM {{%system_config}}")->queryScalar();
        if ($count > 0) {
            echo "    > data already exists in $tableName ...skipping data insertion.\n";
            return true;
        }

        // Insert initial configuration data with dummy/placeholder values
        $time = time();
        $this->batchInsert('{{%system_config}}',
            ['config_key', 'config_value', 'config_type', 'category', 'description', 'is_editable', 'created_at', 'updated_at'],
            [
                // Business Details
                ['businessName', 'Your Company Name', 'string', 'business', 'Business/Company Name', true, $time, $time],
                ['businessAddressLine1', '123 Main Street', 'string', 'business', 'Business Address Line 1', true, $time, $time],
                ['businessAddressLine2', 'Suite 456', 'string', 'business', 'Business Address Line 2', true, $time, $time],
                ['businessCity', 'Colombo', 'string', 'business', 'City', true, $time, $time],
                ['businessPostalCode', '00000', 'string', 'business', 'Postal Code', true, $time, $time],
                ['businessProvince', 'Western Province', 'string', 'business', 'Province', true, $time, $time],

                // Contact Details
                ['adminEmail', 'admin@example.com', 'string', 'business', 'Admin Email Address', true, $time, $time],
                ['senderEmail', 'noreply@example.com', 'string', 'business', 'Sender Email Address', true, $time, $time],
                ['senderName', 'Your Company Name', 'string', 'business', 'Sender Name', true, $time, $time],

                // Banking Details
                ['bankSwiftCode', 'BANKXXXX', 'string', 'banking', 'SWIFT Code', true, $time, $time],
                ['bankName', 'Your Bank Name', 'string', 'banking', 'Bank Name', true, $time, $time],
                ['bankBranchName', 'Main Branch', 'string', 'banking', 'Branch Name', true, $time, $time],
                ['bankCode', '0000', 'string', 'banking', 'Bank Code', true, $time, $time],
                ['bankBranchCode', '000', 'string', 'banking', 'Branch Code', true, $time, $time],
                ['bankAddress', 'Your Bank Address, City, Postal Code, Country', 'string', 'banking', 'Bank Address', true, $time, $time],
                ['bankAccountName', 'Your Company Name', 'string', 'banking', 'Account Name', true, $time, $time],
                ['bankAccountNumber', '000000000000', 'string', 'banking', 'Account Number', true, $time, $time],

                // System Configuration
                ['payrollExpenseCategoryId', '4', 'integer', 'system', 'Payroll Expense Category ID', true, $time, $time],
                ['defaultBankAccountId', '1', 'integer', 'system', 'Default Bank Account ID', true, $time, $time],
                ['currencySymbol', 'Rs. ', 'string', 'system', 'Currency Symbol', true, $time, $time],
                ['attendanceWidgetEnabled', '0', 'boolean', 'system', 'Enable Attendance Widget', true, $time, $time],

                // Invoice Configuration
                ['invoiceNumberStart', '49', 'integer', 'invoice', 'Starting Invoice Number', true, $time, $time],
                ['invoiceNumberFormat', 'INV-%06d', 'string', 'invoice', 'Invoice Number Format', true, $time, $time],

                // Payment Methods (stored as JSON)
                ['paymentMethods', '{"cash":"Cash","bank_transfer":"Bank Transfer","check":"Check","credit_card":"Credit Card"}', 'json', 'system', 'Available Payment Methods', true, $time, $time],

                // Invoice Statuses (stored as JSON)
                ['invoiceStatuses', '{"pending":"Pending","paid":"Paid","overdue":"Overdue","cancelled":"Cancelled"}', 'json', 'system', 'Invoice Status Options', true, $time, $time],

                // Employee Departments (stored as JSON)
                ['departments', '{"management":"Management","finance":"Finance","sales":"Sales","operations":"Operations","hr":"Human Resources","it":"Information Technology","marketing":"Marketing","customer_service":"Customer Service","production":"Production","logistics":"Logistics","legal":"Legal","administration":"Administration"}', 'json', 'system', 'Employee Departments', true, $time, $time],

                // Currencies (stored as JSON)
                ['currencies', '{"LKR":"Sri Lankan Rupee","USD":"US Dollar","EUR":"Euro","GBP":"British Pound","AUD":"Australian Dollar"}', 'json', 'system', 'Supported Currencies', true, $time, $time],

                // Bank Names (stored as JSON)
                ['bankNames', '["Nations Trust Bank","Seylan Bank","National Development Bank","Bank of Ceylon","Commercial Bank","HSBC Bank","Union Bank"]', 'json', 'system', 'List of Banks', true, $time, $time],

                // Signature Details
                ['signatureImage', '', 'string', 'business', 'Signature Image Path', true, $time, $time],
                ['signatureName', 'Authorized Signatory', 'string', 'business', 'Signature Name', true, $time, $time],
                ['signatureTitle', 'Director', 'string', 'business', 'Signature Title/Position', true, $time, $time],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%system_config}}');
    }
}

