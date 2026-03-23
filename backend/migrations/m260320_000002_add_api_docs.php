<?php

use yii\db\Migration;

class m260320_000002_add_api_docs extends Migration
{
    public function safeUp()
    {
        $this->addColumn('mcp_services', 'api_docs', $this->json()->null()->comment('接口文档（JSON 数组）')->after('docs_url'));
    }

    public function safeDown()
    {
        $this->dropColumn('mcp_services', 'api_docs');
    }
}
