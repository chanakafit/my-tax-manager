<?php

use yii\db\Migration;

/**
 * Class m250827_000007_drop_customer_fields_from_invoice
 */
class m250827_000007_drop_customer_fields_from_invoice extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('{{%invoice}}', 'customer_name');
        $this->dropColumn('{{%invoice}}', 'customer_address');
        $this->dropColumn('{{%invoice}}', 'customer_phone');
        $this->dropColumn('{{%invoice}}', 'customer_email');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%invoice}}', 'customer_name', $this->string()->notNull());
        $this->addColumn('{{%invoice}}', 'customer_address', $this->text());
        $this->addColumn('{{%invoice}}', 'customer_phone', $this->string());
        $this->addColumn('{{%invoice}}', 'customer_email', $this->string());
    }
}
