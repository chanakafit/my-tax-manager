<?php

use yii\db\Migration;

/**
 * Class m250827_000011_create_user_table
 */
class m250827_000011_create_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey(),
            'username' => $this->string()->notNull()->unique(),
            'email' => $this->string()->notNull()->unique(),
            'password_hash' => $this->string()->notNull(),
            'auth_key' => $this->string(32)->notNull(),
            'access_token' => $this->string()->unique(),
            'status' => $this->smallInteger()->notNull()->defaultValue(10),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'last_login_at' => $this->integer(),
        ]);

        // Create default admin user
        $defaultPassword = getenv('ADMIN_DEFAULT_PASSWORD');
        $this->insert('{{%user}}', [
            'username' => getenv('ADMIN_DEFAULT_USER'),
            'email' => getenv('ADMIN_DEFAULT_EMAIL'),
            'password_hash' => Yii::$app->security->generatePasswordHash($defaultPassword),
            'auth_key' => Yii::$app->security->generateRandomString(),
            'access_token' => Yii::$app->security->generateRandomString(),
            'status' => 10,
            'created_at' => time(),
            'updated_at' => time(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%user}}');
    }
}
