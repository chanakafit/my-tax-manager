<?php

use yii\db\Migration;

/**
 * Class m250827_000012_remove_tax_rate_from_invoice
 */
class m250827_000012_remove_tax_rate_from_invoice extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('{{%invoice}}', 'tax_rate');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%invoice}}', 'tax_rate', $this->decimal(10, 2)->defaultValue(0)->after('subtotal'));
    }
}
