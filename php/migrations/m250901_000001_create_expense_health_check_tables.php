<?php

use app\base\BaseMigration;

/**
 * Handles the creation of expense health check tables.
 */
class m250901_000001_create_expense_health_check_tables extends BaseMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Table to store suggested missing expenses
        $this->createTable('{{%expense_suggestion}}', [
            'id' => $this->primaryKey(),
            'expense_category_id' => $this->integer()->notNull(),
            'vendor_id' => $this->integer()->notNull(),
            'suggested_month' => $this->date()->notNull()->comment('First day of the month for which expense is suggested'),
            'pattern_months' => $this->text()->notNull()->comment('JSON array of months where this pattern was detected'),
            'avg_amount_lkr' => $this->decimal(15, 2)->defaultValue(0)->comment('Average amount from pattern'),
            'last_expense_id' => $this->integer()->comment('Reference to the most recent expense in the pattern'),
            'status' => $this->string(20)->notNull()->defaultValue('pending')->comment('pending, added, ignored_temporary, ignored_permanent'),
            'ignored_reason' => $this->text()->comment('User provided reason for ignoring'),
            'generated_at' => $this->integer()->notNull()->comment('When this suggestion was generated'),
            'actioned_at' => $this->integer()->comment('When user took action (add/ignore)'),
            'actioned_by' => $this->integer()->comment('User who took action'),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'created_by' => $this->integer()->notNull(),
            'updated_by' => $this->integer()->notNull(),
        ]);

        // Create indexes
        $this->createIndex('idx-expense_suggestion-category_vendor', '{{%expense_suggestion}}', ['expense_category_id', 'vendor_id']);
        $this->createIndex('idx-expense_suggestion-suggested_month', '{{%expense_suggestion}}', 'suggested_month');
        $this->createIndex('idx-expense_suggestion-status', '{{%expense_suggestion}}', 'status');
        $this->createIndex('idx-expense_suggestion-generated_at', '{{%expense_suggestion}}', 'generated_at');

        // Foreign keys
        $this->addForeignKey(
            'fk-expense_suggestion-expense_category_id',
            '{{%expense_suggestion}}',
            'expense_category_id',
            '{{%expense_category}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-expense_suggestion-vendor_id',
            '{{%expense_suggestion}}',
            'vendor_id',
            '{{%vendor}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-expense_suggestion-last_expense_id',
            '{{%expense_suggestion}}',
            'last_expense_id',
            '{{%expense}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        // Unique constraint to prevent duplicate suggestions for same month/category/vendor
        $this->createIndex(
            'idx-expense_suggestion-unique',
            '{{%expense_suggestion}}',
            ['expense_category_id', 'vendor_id', 'suggested_month'],
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%expense_suggestion}}');
    }
}

