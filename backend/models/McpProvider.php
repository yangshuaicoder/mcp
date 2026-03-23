<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $service_id
 * @property string $internal_ip
 * @property string $external_ip
 * @property string $project_name
 * @property string|null $contact
 * @property string $registered_at
 */
class McpProvider extends ActiveRecord
{
    public static function tableName()
    {
        return 'mcp_providers';
    }

    public function getService()
    {
        return $this->hasOne(McpService::class, ['id' => 'service_id']);
    }

    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        return [
            'id'            => $this->id,
            'internal_ip'   => $this->internal_ip,
            'external_ip'   => $this->external_ip,
            'project_name'  => $this->project_name,
            'contact'       => $this->contact,
            'registered_at' => $this->registered_at,
        ];
    }
}
