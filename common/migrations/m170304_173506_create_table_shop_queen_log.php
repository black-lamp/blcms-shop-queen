<?php

use yii\db\Migration;

class m170304_173506_create_table_shop_queen_log extends Migration
{
    public function safeUp()
    {
        $this->createTable('shop_queen_log', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(),
            'entity_name' => $this->string(),
            'entity_id' => $this->integer(),
            'action_id' => $this->integer(),
            'created_at' => $this->dateTime()
        ]);
        $this->addForeignKey('shop_log_user', 'shop_queen_log', 'user_id', 'user', 'id');
    }

    public function safeDown()
    {
        $this->dropForeignKey('shop_log_user', 'shop_queen_log');
        $this->dropTable('shop_queen_log');
    }
}
