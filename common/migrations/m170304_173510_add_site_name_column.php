<?php

use yii\db\Migration;

class m170304_173510_add_site_name_column extends Migration
{
    public function safeUp()
    {
        $this->addColumn('shop_children', 'site_name', $this->string());
    }

    public function safeDown()
    {
        $this->dropColumn('shop_children', 'site_name');
    }
}
