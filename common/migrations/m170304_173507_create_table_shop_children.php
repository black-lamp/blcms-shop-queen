<?php

use yii\db\Migration;

class m170304_173507_create_table_shop_children extends Migration
{
    public function safeUp()
    {
        $this->createTable('shop_children', [
            'id' => $this->primaryKey(),
            'domain_name' => $this->integer(),
            'company_id' => $this->integer(),
            'updated_at' => $this->dateTime(),
            'created_at' => $this->dateTime()
        ]);
        $this->addForeignKey('shop_children_company', 'shop_children', 'company_id', 'partner_company', 'id');
    }

    public function safeDown()
    {
        $this->dropForeignKey('shop_children_company', 'shop_children');
        $this->dropTable('shop_children');
    }
}
