<?php

use yii\db\Migration;

class m170304_173508_create_table_shop_children_synch extends Migration
{
    public function safeUp()
    {
        $this->createTable('shop_children_synch', [
            'id' => $this->primaryKey(),
            'queen_log_id' => $this->integer(),
            'child_id' => $this->integer(),
            'status' => $this->integer(),
            'updated_at' => $this->dateTime(),
            'created_at' => $this->dateTime()
        ]);
        $this->addForeignKey('shop_children_synch_queen_log', 'shop_children_synch', 'queen_log_id', 'shop_queen_log', 'id');
        $this->addForeignKey('shop_children_synch_child', 'shop_children_synch', 'child_id', 'shop_children', 'id');
    }

    public function safeDown()
    {
        $this->dropForeignKey('shop_children_synch_queen_log', 'shop_children_synch');
        $this->dropForeignKey('shop_children_synch_child', 'shop_children_synch');
        $this->dropTable('shop_children_synch');
    }
}
