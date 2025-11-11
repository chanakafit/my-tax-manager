<?php

use yii\db\Migration;

/**
 * Handles adding account_holder_type column to table `{{%bank_account}}`.
 */
class m251109_000002_add_account_holder_type_to_bank_account extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%bank_account}}', 'account_holder_type', $this->string(20)->notNull()->defaultValue('business')->after('account_type'));

        // Add index for filtering
        $this->createIndex('idx-bank_account-account_holder_type', '{{%bank_account}}', 'account_holder_type');

        // Add comment
        $this->addCommentOnColumn('{{%bank_account}}', 'account_holder_type', 'Type: business or personal');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-bank_account-account_holder_type', '{{%bank_account}}');
        $this->dropColumn('{{%bank_account}}', 'account_holder_type');
    }
}

