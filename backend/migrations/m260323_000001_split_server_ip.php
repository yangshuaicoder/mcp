<?php

use yii\db\Migration;

/**
 * mcp_providers: server_ip → internal_ip + external_ip
 */
class m260323_000001_split_server_ip extends Migration
{
    public function safeUp()
    {
        // 1. 重命名 server_ip → internal_ip
        $this->renameColumn('mcp_providers', 'server_ip', 'internal_ip');

        // 2. 新增 external_ip
        $this->addColumn('mcp_providers', 'external_ip',
            $this->string(100)->notNull()->defaultValue('')->comment('外网 IP')->after('internal_ip')
        );

        // 3. 重建唯一索引
        $this->dropIndex('uq_service_server', 'mcp_providers');
        $this->createIndex('uq_service_internal_ip', 'mcp_providers', ['service_id', 'internal_ip'], true);
    }

    public function safeDown()
    {
        $this->dropIndex('uq_service_internal_ip', 'mcp_providers');
        $this->dropColumn('mcp_providers', 'external_ip');
        $this->renameColumn('mcp_providers', 'internal_ip', 'server_ip');
        $this->createIndex('uq_service_server', 'mcp_providers', ['service_id', 'server_ip'], true);
    }
}
