<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%tax_year_snapshot}}`.
 * This table stores year-end balances for tax return submission.
 */
class m251109_000004_create_tax_year_snapshot_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%tax_year_snapshot}}', [
            'id' => $this->primaryKey(),
            'tax_year' => $this->string(4)->notNull(), // e.g., '2023' for 2023-2024
            'snapshot_date' => $this->date()->notNull(), // Usually March 31
            'notes' => $this->text(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'created_by' => $this->integer()->notNull(),
            'updated_by' => $this->integer()->notNull(),
        ]);

        // Create table for bank account balances at year-end
        $this->createTable('{{%tax_year_bank_balance}}', [
            'id' => $this->primaryKey(),
            'tax_year_snapshot_id' => $this->integer()->notNull(),
            'bank_account_id' => $this->integer()->notNull(),
            'balance' => $this->decimal(15, 2)->notNull(),
            'balance_lkr' => $this->decimal(15, 2)->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        // Create table for liability balances at year-end
        $this->createTable('{{%tax_year_liability_balance}}', [
            'id' => $this->primaryKey(),
            'tax_year_snapshot_id' => $this->integer()->notNull(),
            'liability_id' => $this->integer()->notNull(),
            'outstanding_balance' => $this->decimal(15, 2)->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        // Add foreign keys
        $this->addForeignKey(
            'fk-tax_year_bank_balance-snapshot',
            '{{%tax_year_bank_balance}}',
            'tax_year_snapshot_id',
            '{{%tax_year_snapshot}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-tax_year_bank_balance-bank_account',
            '{{%tax_year_bank_balance}}',
            'bank_account_id',
            '{{%bank_account}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-tax_year_liability_balance-snapshot',
            '{{%tax_year_liability_balance}}',
            'tax_year_snapshot_id',
            '{{%tax_year_snapshot}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-tax_year_liability_balance-liability',
            '{{%tax_year_liability_balance}}',
            'liability_id',
            '{{%liability}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Create indexes
        $this->createIndex('idx-tax_year_snapshot-tax_year', '{{%tax_year_snapshot}}', 'tax_year', true);
        $this->createIndex('idx-tax_year_bank_balance-snapshot', '{{%tax_year_bank_balance}}', 'tax_year_snapshot_id');
        $this->createIndex('idx-tax_year_liability_balance-snapshot', '{{%tax_year_liability_balance}}', 'tax_year_snapshot_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%tax_year_liability_balance}}');
        $this->dropTable('{{%tax_year_bank_balance}}');
        $this->dropTable('{{%tax_year_snapshot}}');
    }
}

