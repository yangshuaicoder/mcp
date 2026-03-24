#!/bin/bash
# 项目：MCP管理后台
# 维护人：杨帅
# 最后修改：2026-03-24
#
# 用法：./mcp.sh [start|stop|restart|status|log]
#
# 说明：MCP 后端为 PHP-FPM（由系统管理），此脚本管理 Nginx 站点配置的生效/撤销

APP_NAME="mcp"
NGINX_CONF="/etc/nginx/conf.d/${APP_NAME}.conf"
NGINX_CONF_DISABLED="/etc/nginx/conf.d/${APP_NAME}.conf.disabled"
LOG_DIR="/data/logs/${APP_NAME}"
ACCESS_LOG="${LOG_DIR}/access.log"
ERROR_LOG="${LOG_DIR}/error.log"
PORT=8083

start() {
    if [ -f "${NGINX_CONF_DISABLED}" ]; then
        mv "${NGINX_CONF_DISABLED}" "${NGINX_CONF}"
        echo "[mcp] nginx 配置已启用"
    fi
    nginx -t && nginx -s reload
    echo "[mcp] 已启动，端口 ${PORT}"
}

stop() {
    if [ -f "${NGINX_CONF}" ]; then
        mv "${NGINX_CONF}" "${NGINX_CONF_DISABLED}"
        nginx -t && nginx -s reload
        echo "[mcp] 已停止（nginx 配置已禁用）"
    else
        echo "[mcp] 未运行"
    fi
}

restart() {
    nginx -t && nginx -s reload
    echo "[mcp] nginx 已重载"
}

status() {
    if ss -tlnp | grep -q ":${PORT}"; then
        echo "[mcp] 运行中，端口 ${PORT}"
    else
        echo "[mcp] 未运行"
    fi
    echo "PHP-FPM: $(systemctl is-active php-fpm 2>/dev/null || echo '未知')"
    echo "Nginx:   $(systemctl is-active nginx 2>/dev/null || echo '未知')"
}

log() {
    echo "=== access.log ==="
    tail -50 "${ACCESS_LOG}" 2>/dev/null || echo "日志不存在: ${ACCESS_LOG}"
    echo "=== error.log ==="
    tail -50 "${ERROR_LOG}" 2>/dev/null || echo "日志不存在: ${ERROR_LOG}"
}

case "$1" in
    start)   start ;;
    stop)    stop ;;
    restart) restart ;;
    status)  status ;;
    log)     log ;;
    *)
        echo "用法: $0 {start|stop|restart|status|log}"
        exit 1
        ;;
esac
