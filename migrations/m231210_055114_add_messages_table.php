<?php

use yii\db\Migration;

/**
 * Class m231210_055114_add_messages_table
 */
class m231210_055114_add_messages_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('messages', [
           'id' => $this->primaryKey(),
           'parent_id' => $this->integer(),
           'message' => $this->text(),
           'type' => $this->integer()->notNull()->defaultValue(0),
           'from_user' => $this->integer()->notNull(),
           'to_user' => $this->integer(),
           'created_at' => $this->integer(11),
           'updated_at' => $this->integer(11)
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('messages');
    }

}
