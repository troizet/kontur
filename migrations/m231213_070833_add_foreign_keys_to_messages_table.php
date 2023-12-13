<?php

use yii\db\Migration;

/**
 * Class m231213_070833_add_foreign_keys_to_messages_table
 */
class m231213_070833_add_foreign_keys_to_messages_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addForeignKey(
            'fk-messge-from_user',
            'messages',
            'from_user',
            'users',
            'id',
            'RESTRICT'
        );

        $this->addForeignKey(
            'fk-messge-to_user',
            'messages',
            'to_user',
            'users',
            'id',
            'RESTRICT'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-messge-from_user', 'messages');
        $this->dropForeignKey('fk-messge-to_user', 'messages');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m231213_070833_add_foreign_keys_to_messages_table cannot be reverted.\n";

        return false;
    }
    */
}
