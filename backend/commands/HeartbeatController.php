<?php

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use app\models\McpService;

/**
 * 心跳超时检测命令
 * 用法：php yii heartbeat/check
 * crontab：* * * * * cd /data/apps/mcp/current/backend && php yii heartbeat/check >> /data/logs/cron/mcp_heartbeat_check.log 2>&1
 */
class HeartbeatController extends Controller
{
    public function actionCheck()
    {
        $timeout = \Yii::$app->params['heartbeat_timeout'] ?? 180;
        $deadline = date('Y-m-d H:i:s', time() - $timeout);

        $count = McpService::updateAll(
            ['status' => McpService::STATUS_OFFLINE],
            ['and',
                ['status' => McpService::STATUS_ONLINE],
                ['not', ['last_heartbeat_at' => null]],
                ['<', 'last_heartbeat_at', $deadline],
            ]
        );

        if ($count > 0) {
            echo date('Y-m-d H:i:s') . " [heartbeat] {$count} service(s) set to offline (timeout: {$timeout}s)\n";
        }

        return ExitCode::OK;
    }
}
