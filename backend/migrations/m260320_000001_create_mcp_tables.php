<?php

use yii\db\Migration;

class m260320_000001_create_mcp_tables extends Migration
{
    public function safeUp()
    {
        $this->createTable('mcp_services', [
            'id'                => $this->primaryKey()->unsigned(),
            'name'              => $this->string(100)->notNull()->unique()->comment('服务唯一标识，如 payment.v1'),
            'display_name'      => $this->string(200)->notNull()->comment('展示名'),
            'category'          => $this->string(50)->notNull()->defaultValue('other')->comment('分类（提供方自报）'),
            'description'       => $this->text()->null()->comment('服务描述'),
            'base_url'          => $this->string(500)->notNull()->comment('服务根地址'),
            'docs_url'          => $this->string(500)->null()->comment('文档地址'),
            'status'            => "ENUM('online','offline') NOT NULL DEFAULT 'offline'",
            'last_heartbeat_at' => $this->dateTime()->null()->comment('最后心跳时间'),
            'created_at'        => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at'        => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT=\'MCP服务主表\'');

        $this->createIndex('idx_status',   'mcp_services', 'status');
        $this->createIndex('idx_category', 'mcp_services', 'category');

        $this->createTable('mcp_providers', [
            'id'            => $this->primaryKey()->unsigned(),
            'service_id'    => $this->integer()->unsigned()->notNull()->comment('关联 mcp_services.id'),
            'server_ip'     => $this->string(100)->notNull()->comment('提供方服务器 IP'),
            'project_name'  => $this->string(200)->notNull()->comment('提供方项目名'),
            'contact'       => $this->string(200)->null()->comment('负责人'),
            'registered_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT=\'服务提供方\'');

        $this->createIndex('uq_service_server', 'mcp_providers', ['service_id', 'server_ip'], true);
        $this->createIndex('idx_service_id',    'mcp_providers', 'service_id');

        $this->createTable('mcp_consumers', [
            'id'            => $this->primaryKey()->unsigned(),
            'service_id'    => $this->integer()->unsigned()->notNull()->comment('关联 mcp_services.id'),
            'server_ip'     => $this->string(100)->notNull()->comment('消费方服务器 IP'),
            'project_name'  => $this->string(200)->notNull()->comment('消费方项目名'),
            'contact'       => $this->string(200)->null()->comment('负责人'),
            'registered_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT=\'服务消费方\'');

        $this->createIndex('uq_service_consumer', 'mcp_consumers', ['service_id', 'server_ip', 'project_name'], true);
        $this->createIndex('idx_service_id',       'mcp_consumers', 'service_id');
    }

    public function safeDown()
    {
        $this->dropTable('mcp_consumers');
        $this->dropTable('mcp_providers');
        $this->dropTable('mcp_services');
    }
}
