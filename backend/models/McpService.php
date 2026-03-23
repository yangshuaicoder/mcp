<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $name
 * @property string $display_name
 * @property string $category
 * @property string|null $description
 * @property string $base_url
 * @property string|null $docs_url
 * @property array|null $api_docs  接口文档 JSON
 * @property string $status  online|offline
 * @property string|null $last_heartbeat_at
 * @property string $created_at
 * @property string $updated_at
 */
class McpService extends ActiveRecord
{
    const STATUS_ONLINE  = 'online';
    const STATUS_OFFLINE = 'offline';

    public static function tableName()
    {
        return 'mcp_services';
    }

    public function rules()
    {
        return [
            [['name', 'display_name', 'base_url'], 'required'],
            [['name'], 'string', 'max' => 100],
            [['display_name'], 'string', 'max' => 200],
            [['category'], 'string', 'max' => 50],
            [['base_url', 'docs_url'], 'string', 'max' => 500],
            [['description'], 'string'],
            [['api_docs'], 'safe'],
            [['status'], 'in', 'range' => [self::STATUS_ONLINE, self::STATUS_OFFLINE]],
        ];
    }

    public function getProviders()
    {
        return $this->hasMany(McpProvider::class, ['service_id' => 'id']);
    }

    public function getConsumers()
    {
        return $this->hasMany(McpConsumer::class, ['service_id' => 'id']);
    }

    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'display_name'     => $this->display_name,
            'category'         => $this->category,
            'description'      => $this->description,
            'base_url'         => $this->base_url,
            'docs_url'         => $this->docs_url,
            'api_docs'         => is_string($this->api_docs) ? json_decode($this->api_docs, true) : $this->api_docs,
            'status'           => $this->status,
            'last_heartbeat_at'=> $this->last_heartbeat_at,
            'created_at'       => $this->created_at,
            'updated_at'       => $this->updated_at,
        ];
    }
}
