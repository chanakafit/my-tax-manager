<?php

use app\base\BaseMigration;

/**
 * Handles adding asset_type column to table `{{%capital_asset}}`.
 */
class m251109_000001_add_asset_type_to_capital_asset extends BaseMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%capital_asset}}', 'asset_type', $this->string(20)->notNull()->defaultValue('business')->after('asset_name'));
        $this->addColumn('{{%capital_asset}}', 'asset_category', $this->string(50)->after('asset_type'));

        // Add index for filtering
        $this->createIndex('idx-capital_asset-asset_type', '{{%capital_asset}}', 'asset_type');

        // Add comments
        $this->addCommentOnColumn('{{%capital_asset}}', 'asset_type', 'Type: business or personal');
        $this->addCommentOnColumn('{{%capital_asset}}', 'asset_category', 'Category: immovable, movable, etc.');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-capital_asset-asset_type', '{{%capital_asset}}');
        $this->dropColumn('{{%capital_asset}}', 'asset_category');
        $this->dropColumn('{{%capital_asset}}', 'asset_type');
    }
}

