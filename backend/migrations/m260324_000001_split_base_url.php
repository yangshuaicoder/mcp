<?php

use yii\db\Migration;

/**
 * 将 mcp_services.base_url 拆分为 internal_url（内网地址）和 external_url（外网地址）
 */
class m260324_000001_split_base_url extends Migration
{
    public function safeUp()
    {
        // 重命名 base_url → internal_url
        $this->renameColumn('mcp_services', 'base_url', 'internal_url');

        // 添加 external_url
        $this->addColumn(
            'mcp_services',
            'external_url',
            $this->string(500)->notNull()->defaultValue('')->comment('外网访问地址')->after('internal_url')
        );
    }

    public function safeDown()
    {
        $this->dropColumn('mcp_services', 'external_url');
        $this->renameColumn('mcp_services', 'internal_url', 'base_url');
    }
}
