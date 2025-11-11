<?php

use yii\db\Migration;

/**
 * Separates bank accounts into owner accounts and employee accounts
 */
class m251109_000006_separate_owner_and_employee_bank_accounts extends Migration
{
    public function safeUp()
    {
        // Create new owner_bank_account table for business owner's accounts
        $this->createTable('{{%owner_bank_account}}', [
            'id' => $this->primaryKey(),
            'account_name' => $this->string()->notNull(),
            'account_number' => $this->string()->notNull(),
            'bank_name' => $this->string()->notNull(),
            'branch_name' => $this->string(),
            'swift_code' => $this->string(),
            'account_type' => $this->string()->notNull(), // savings, current, etc.
            'account_holder_type' => $this->string(20)->notNull()->defaultValue('business'), // business or personal
            'currency' => $this->string(3)->notNull()->defaultValue('LKR'),
            'is_active' => $this->boolean()->defaultValue(true),
            'notes' => $this->text(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'created_by' => $this->integer()->notNull(),
            'updated_by' => $this->integer()->notNull(),
        ]);

        // Create indexes
        $this->createIndex('idx-owner_bank_account-account_number', '{{%owner_bank_account}}', 'account_number', true);
        $this->createIndex('idx-owner_bank_account-is_active', '{{%owner_bank_account}}', 'is_active');
        $this->createIndex('idx-owner_bank_account-account_holder_type', '{{%owner_bank_account}}', 'account_holder_type');

        // Migrate existing bank accounts that are NOT linked to employees to owner_bank_account
        // First, get all bank_account IDs that are referenced by employee_payroll_details
        $employeeBankAccountIds = $this->db->createCommand(
            'SELECT DISTINCT bank_account_id FROM {{%employee_payroll_details}}'
        )->queryColumn();

        // Copy non-employee bank accounts to owner_bank_account
        if (!empty($employeeBankAccountIds)) {
            $idsList = implode(',', $employeeBankAccountIds);
            $this->execute("
                INSERT INTO {{%owner_bank_account}} 
                    (account_name, account_number, bank_name, branch_name, swift_code, 
                     account_type, account_holder_type, currency, is_active, notes, 
                     created_at, updated_at, created_by, updated_by)
                SELECT 
                    account_name, account_number, bank_name, branch_name, swift_code,
                    account_type, account_holder_type, currency, is_active, notes,
                    created_at, updated_at, created_by, updated_by
                FROM {{%bank_account}}
                WHERE id NOT IN ($idsList)
            ");
        } else {
            // No employee accounts, copy all
            $this->execute("
                INSERT INTO {{%owner_bank_account}} 
                    (account_name, account_number, bank_name, branch_name, swift_code, 
                     account_type, account_holder_type, currency, is_active, notes, 
                     created_at, updated_at, created_by, updated_by)
                SELECT 
                    account_name, account_number, bank_name, branch_name, swift_code,
                    account_type, account_holder_type, currency, is_active, notes,
                    created_at, updated_at, created_by, updated_by
                FROM {{%bank_account}}
            ");
        }

        // Update financial_transaction to use owner_bank_account
        // Create temporary mapping table
        $this->createTable('{{%temp_bank_mapping}}', [
            'old_id' => $this->integer(),
            'new_id' => $this->integer(),
        ]);

        // Build mapping from old bank_account to new owner_bank_account based on account_number
        $this->execute("
            INSERT INTO {{%temp_bank_mapping}} (old_id, new_id)
            SELECT ba.id, oba.id
            FROM {{%bank_account}} ba
            JOIN {{%owner_bank_account}} oba ON ba.account_number = oba.account_number
        ");

        // Update financial_transaction references
        $this->execute("
            UPDATE {{%financial_transaction}} ft
            JOIN {{%temp_bank_mapping}} tm ON ft.bank_account_id = tm.old_id
            SET ft.bank_account_id = tm.new_id
        ");

        // Update tax_year_bank_balance references
        $this->execute("
            UPDATE {{%tax_year_bank_balance}} tybb
            JOIN {{%temp_bank_mapping}} tm ON tybb.bank_account_id = tm.old_id
            SET tybb.bank_account_id = tm.new_id
        ");

        // Drop temporary mapping table
        $this->dropTable('{{%temp_bank_mapping}}');

        // Remove account_holder_type from bank_account as it's no longer needed
        // (bank_account now only stores employee accounts)
        $this->dropIndex('idx-bank_account-account_holder_type', '{{%bank_account}}');
        $this->dropColumn('{{%bank_account}}', 'account_holder_type');

        // Delete non-employee bank accounts from bank_account table
        if (!empty($employeeBankAccountIds)) {
            $idsList = implode(',', $employeeBankAccountIds);
            $this->execute("DELETE FROM {{%bank_account}} WHERE id NOT IN ($idsList)");
        } else {
            // No employee accounts, delete all from bank_account (they're now in owner_bank_account)
            $this->execute("DELETE FROM {{%bank_account}}");
        }

        // Update foreign keys for financial_transaction
        $this->dropForeignKey('fk-tax_year_bank_balance-bank_account', '{{%tax_year_bank_balance}}');
        $this->addForeignKey(
            'fk-tax_year_bank_balance-owner_bank_account',
            '{{%tax_year_bank_balance}}',
            'bank_account_id',
            '{{%owner_bank_account}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        // Restore account_holder_type to bank_account
        $this->addColumn('{{%bank_account}}', 'account_holder_type', $this->string(20)->notNull()->defaultValue('business')->after('account_type'));
        $this->createIndex('idx-bank_account-account_holder_type', '{{%bank_account}}', 'account_holder_type');

        // Copy owner bank accounts back to bank_account
        $this->execute("
            INSERT INTO {{%bank_account}} 
                (account_name, account_number, bank_name, branch_name, swift_code, 
                 account_type, account_holder_type, currency, is_active, notes, 
                 created_at, updated_at, created_by, updated_by)
            SELECT 
                account_name, account_number, bank_name, branch_name, swift_code,
                account_type, account_holder_type, currency, is_active, notes,
                created_at, updated_at, created_by, updated_by
            FROM {{%owner_bank_account}}
        ");

        // Update foreign keys back
        $this->dropForeignKey('fk-tax_year_bank_balance-owner_bank_account', '{{%tax_year_bank_balance}}');
        $this->addForeignKey(
            'fk-tax_year_bank_balance-bank_account',
            '{{%tax_year_bank_balance}}',
            'bank_account_id',
            '{{%bank_account}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Drop owner_bank_account table
        $this->dropTable('{{%owner_bank_account}}');
    }
}

