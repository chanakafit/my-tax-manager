<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%tax_return_support_document}}`.
 */
class m251118_165245_create_tax_return_support_document_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%tax_return_support_document}}', [
            'id' => $this->primaryKey(),
            'tax_year_snapshot_id' => $this->integer()->notNull(),
            'document_title' => $this->string(255)->notNull(),
            'document_description' => $this->text()->null(),
            'file_path' => $this->string(500)->notNull(),
            'file_name' => $this->string(255)->notNull(),
            'file_size' => $this->integer()->notNull(),
            'mime_type' => $this->string(100)->null(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'created_by' => $this->integer()->notNull(),
            'updated_by' => $this->integer()->notNull(),
        ]);

        // Add indexes
        $this->createIndex(
            'idx-tax_return_support_document-tax_year_snapshot_id',
            '{{%tax_return_support_document}}',
            'tax_year_snapshot_id'
        );

        $this->createIndex(
            'idx-tax_return_support_document-created_at',
            '{{%tax_return_support_document}}',
            'created_at'
        );

        // Add foreign key
        $this->addForeignKey(
            'fk-tax_return_support_document-tax_year_snapshot_id',
            '{{%tax_return_support_document}}',
            'tax_year_snapshot_id',
            '{{%tax_year_snapshot}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-tax_return_support_document-created_by',
            '{{%tax_return_support_document}}',
            'created_by',
            '{{%user}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-tax_return_support_document-updated_by',
            '{{%tax_return_support_document}}',
            'updated_by',
            '{{%user}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-tax_return_support_document-updated_by', '{{%tax_return_support_document}}');
        $this->dropForeignKey('fk-tax_return_support_document-created_by', '{{%tax_return_support_document}}');
        $this->dropForeignKey('fk-tax_return_support_document-tax_year_snapshot_id', '{{%tax_return_support_document}}');

        $this->dropIndex('idx-tax_return_support_document-created_at', '{{%tax_return_support_document}}');
        $this->dropIndex('idx-tax_return_support_document-tax_year_snapshot_id', '{{%tax_return_support_document}}');

        $this->dropTable('{{%tax_return_support_document}}');
    }
}
