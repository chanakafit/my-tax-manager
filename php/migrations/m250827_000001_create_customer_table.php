<?php

use yii\db\Migration;

/**
 * Class m250827_000001_create_customer_table
 * Handles the creation of table `customer`
 */
class m250827_000001_create_customer_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%customer}}', [
            'id' => $this->primaryKey(),
            'company_name' => $this->string()->notNull(),
            'contact_person' => $this->string(),
            'email' => $this->string()->notNull(),
            'phone' => $this->string(),
            'address' => $this->text(),
            'city' => $this->string(),
            'state' => $this->string(),
            'postal_code' => $this->string(),
            'country' => $this->string(),
            'tax_number' => $this->string()->comment('VAT/Tax registration number'),
            'website' => $this->string(),
            'notes' => $this->text(),
            'status' => $this->smallInteger()->notNull()->defaultValue(10),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ]);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%customer}}');
    }
}
