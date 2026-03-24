<?php

namespace app\modules\api\controllers;

use app\services\BaseApiController;
use app\models\McpService;
use app\models\McpConsumer;

class ConsumerController extends BaseApiController
{
    /**
     * POST /api/v1/consumers/register
     * 消费方登记
     */
    public function actionRegister()
    {
        $body        = $this->getJsonBody();
        $serviceName = trim($body['service_name'] ?? '');
        $serverIp    = trim($body['server_ip'] ?? '');
        $projectName = trim($body['project_name'] ?? '');

        if (!$serviceName || !$serverIp || !$projectName) {
            return $this->error('service_name, server_ip, project_name 为必填项', 1001);
        }

        $service = McpService::findOne(['name' => $serviceName]);
        if (!$service) {
            return $this->error('服务不存在: ' . $serviceName, 1004);
        }

        $consumer = McpConsumer::findOne([
            'service_id'   => $service->id,
            'server_ip'    => $serverIp,
            'project_name' => $projectName,
        ]);

        if (!$consumer) {
            $consumer = new McpConsumer();
            $consumer->service_id   = $service->id;
            $consumer->server_ip    = $serverIp;
            $consumer->project_name = $projectName;
        }

        $consumer->contact = trim($body['contact'] ?? '') ?: null;
        if (!$consumer->save(false)) {
            return $this->error('消费方登记失败: ' . json_encode($consumer->errors), 5001);
        }

        return $this->success();
    }
}
