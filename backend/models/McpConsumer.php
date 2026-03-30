<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $service_id
 * @property string $server_ip
 * @property string $project_name
 * @property string|null $contact
 * @property string $registered_at
 */
class McpConsumer extends ActiveRecord
{
    public static function tableName()
    {
        return 'mcp_consumers';
    }

    public function rules()
    {
        return [
            [['service_id', 'server_ip', 'project_name'], 'required'],
            [['service_id'], 'integer'],
            [['server_ip'], 'string', 'max' => 100],
            [['project_name'], 'string', 'max' => 200],
            [['contact'], 'string', 'max' => 200],
            [['contact'], 'default', 'value' => null],
        ];
    }

    public function getService()
    {
        return $this->hasOne(McpService::class, ['id' => 'service_id']);
    }

    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        return [
            'id'            => $this->id,
            'server_ip'     => $this->server_ip,
            'project_name'  => $this->project_name,
            'contact'       => $this->contact,
            'registered_at' => $this->registered_at,
        ];
    }
}
