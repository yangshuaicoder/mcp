#!/bin/bash
# 项目：MCP管理后台
# 维护人：杨帅
# 最后修改：2026-03-24
# 触发时间：每分钟执行一次（crontab: * * * * *）
# 用途：检测超过 3 分钟未发送心跳的服务，自动标记为 offline

cd /data/apps/mcp/current/backend && php yii heartbeat/check
