<?php

namespace app\modules\api\controllers;

use app\services\BaseApiController;
use app\models\McpService;
use app\models\McpProvider;
use yii\db\Expression;

class ServiceController extends BaseApiController
{
    /**
     * POST /api/v1/services/register
     * 服务注册 / 更新
     */
    public function actionRegister()
    {
        $body = $this->getJsonBody();

        $name        = trim($body['name'] ?? '');
        $displayName = trim($body['display_name'] ?? '');
        $baseUrl     = trim($body['base_url'] ?? '');
        $serverIp    = trim($body['server_ip'] ?? '');
        $projectName = trim($body['project_name'] ?? '');

        if (!$name || !$displayName || !$baseUrl || !$serverIp || !$projectName) {
            return $this->error('name, display_name, base_url, server_ip, project_name 为必填项', 1001);
        }

        // Upsert service
        $service = McpService::findOne(['name' => $name]);
        if (!$service) {
            $service = new McpService();
            $service->name = $name;
        }

        $service->display_name      = $displayName;
        $service->category          = trim($body['category'] ?? 'other') ?: 'other';
        $service->description       = trim($body['description'] ?? '') ?: null;
        $service->base_url          = $baseUrl;
        $service->docs_url          = trim($body['docs_url'] ?? '') ?: null;
        $service->status            = McpService::STATUS_ONLINE;
        $service->last_heartbeat_at = date('Y-m-d H:i:s');

        if (!$service->save()) {
            return $this->error('保存失败: ' . json_encode($service->errors), 5001);
        }

        // Upsert provider
        $provider = McpProvider::findOne(['service_id' => $service->id, 'server_ip' => $serverIp]);
        if (!$provider) {
            $provider = new McpProvider();
            $provider->service_id = $service->id;
            $provider->server_ip  = $serverIp;
        }
        $provider->project_name = $projectName;
        $provider->contact      = trim($body['contact'] ?? '') ?: null;
        $provider->save(false);

        return $this->success([
            'id'     => $service->id,
            'name'   => $service->name,
            'status' => $service->status,
        ]);
    }

    /**
     * POST /api/v1/services/heartbeat
     * 心跳上报
     */
    public function actionHeartbeat()
    {
        $body     = $this->getJsonBody();
        $name     = trim($body['name'] ?? '');
        $serverIp = trim($body['server_ip'] ?? '');

        if (!$name || !$serverIp) {
            return $this->error('name, server_ip 为必填项', 1001);
        }

        $service = McpService::findOne(['name' => $name]);
        if (!$service) {
            return $this->error('服务不存在，请先注册', 1004);
        }

        $service->last_heartbeat_at = date('Y-m-d H:i:s');
        $service->status = McpService::STATUS_ONLINE;
        $service->save(false);

        return $this->success();
    }

    /**
     * POST /api/v1/services/unregister
     * 服务注销：删除对应 provider，若无 provider 则 service 置为 offline
     */
    public function actionUnregister()
    {
        $body     = $this->getJsonBody();
        $name     = trim($body['name'] ?? '');
        $serverIp = trim($body['server_ip'] ?? '');

        if (!$name || !$serverIp) {
            return $this->error('name, server_ip 为必填项', 1001);
        }

        $service = McpService::findOne(['name' => $name]);
        if (!$service) {
            return $this->error('服务不存在', 1004);
        }

        // 删除该 server_ip 对应的 provider 记录
        $provider = McpProvider::findOne(['service_id' => $service->id, 'server_ip' => $serverIp]);
        if ($provider) {
            $provider->delete();
        }

        // 若该服务已无任何 provider，则置为 offline
        $remainingProviders = McpProvider::find()->where(['service_id' => $service->id])->count();
        if ($remainingProviders == 0) {
            $service->status = McpService::STATUS_OFFLINE;
            $service->save(false);
        }

        return $this->success([
            'name'               => $service->name,
            'status'             => $service->status,
            'remaining_providers' => (int)$remainingProviders,
        ]);
    }

    /**
     * GET /api/v1/services
     * 服务列表
     */
    public function actionIndex()
    {
        $request  = \Yii::$app->request;
        $category = trim($request->get('category', ''));
        $keyword  = trim($request->get('keyword', ''));
        $status   = trim($request->get('status', 'online'));
        $page     = max(1, (int)$request->get('page', 1));
        $pageSize = min(100, max(1, (int)$request->get('page_size', 20)));

        $query = McpService::find();

        if ($status && $status !== 'all') {
            $query->andWhere(['status' => $status]);
        }
        if ($category) {
            $query->andWhere(['category' => $category]);
        }
        if ($keyword) {
            $query->andWhere(['or',
                ['like', 'name', $keyword],
                ['like', 'display_name', $keyword],
                ['like', 'description', $keyword],
            ]);
        }

        $total = $query->count();
        $services = $query
            ->orderBy(['status' => SORT_DESC, 'updated_at' => SORT_DESC])
            ->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->all();

        return $this->success([
            'list'      => array_map(fn($s) => $s->toArray(), $services),
            'total'     => (int)$total,
            'page'      => $page,
            'page_size' => $pageSize,
        ]);
    }

    /**
     * GET /api/v1/services/{name}
     * 服务详情
     */
    public function actionDetail($name)
    {
        $service = McpService::findOne(['name' => $name]);
        if (!$service) {
            return $this->error('服务不存在', 1004);
        }

        $providers = McpProvider::find()
            ->where(['service_id' => $service->id])
            ->orderBy(['registered_at' => SORT_DESC])
            ->all();

        $consumers = \app\models\McpConsumer::find()
            ->where(['service_id' => $service->id])
            ->orderBy(['registered_at' => SORT_DESC])
            ->all();

        return $this->success([
            'service'   => $service->toArray(),
            'providers' => array_map(fn($p) => $p->toArray(), $providers),
            'consumers' => array_map(fn($c) => $c->toArray(), $consumers),
        ]);
    }
}
