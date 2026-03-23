# MCP 服务提供方接入文档

> 版本：v1.0 | 日期：2026-03-20

本文档面向 **微服务提供方**（拥有微服务的团队/开发者），指导如何将你的服务注册到 MCP 平台。

## MCP 地址

| 环境 | 地址 |
|------|------|
| **外网** | `http://43.142.159.201:8083` |
| **内网** | `http://10.20.240.84:8083` |

## 接口规范

- Base Path: `/api/v1`
- 请求格式: `Content-Type: application/json`
- 响应格式: 统一 JSON `{"code": 0, "message": "ok", "data": {}}`
- code=0 表示成功，非 0 表示错误
- 无需鉴权（v1.0）

---

## 接入流程

```
服务启动 → 调用「注册接口」→ 定时调用「心跳接口」(30~60s) → 服务关闭时调用「注销接口」
```

---

## 1. 服务注册

**POST** `/api/v1/services/register`

服务启动时调用此接口。name 相同则更新信息，不同则新建。注册成功后状态自动为 online。

**Request Body:**

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| name | string | 是 | 服务唯一标识，建议点分命名如 `payment.v1`、`ai.image.v2` |
| display_name | string | 是 | 展示名称，如 `支付服务` |
| category | string | 否 | 服务分类（自由填写），默认 `other` |
| description | string | 否 | 服务描述 |
| base_url | string | 是 | 服务根地址，如 `http://10.0.0.1:8080` |
| docs_url | string | 否 | 接口文档地址 |
| server_ip | string | 是 | 当前提供方服务器 IP |
| project_name | string | 是 | 当前项目名称 |
| contact | string | 否 | 负责人 |

**示例：**

```bash
curl -X POST http://10.20.240.84:8083/api/v1/services/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "payment.v1",
    "display_name": "支付服务",
    "category": "payment",
    "description": "内部支付微服务，支持支付宝/微信支付",
    "base_url": "http://10.0.0.1:8080",
    "docs_url": "http://10.0.0.1:8080/docs",
    "server_ip": "10.0.0.1",
    "project_name": "uni-pay",
    "contact": "张三"
  }'
```

**响应：**

```json
{
  "code": 0,
  "message": "ok",
  "data": { "id": 1, "name": "payment.v1", "status": "online" }
}
```

---

## 2. 心跳上报

**POST** `/api/v1/services/heartbeat`

注册成功后，需定时发送心跳（建议 30~60 秒一次）。**超过 3 分钟未收到心跳，系统自动将服务标记为 offline。**

**Request Body:**

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| name | string | 是 | 服务唯一标识 |
| server_ip | string | 是 | 提供方服务器 IP |

**示例：**

```bash
curl -X POST http://10.20.240.84:8083/api/v1/services/heartbeat \
  -H "Content-Type: application/json" \
  -d '{"name": "payment.v1", "server_ip": "10.0.0.1"}'
```

**响应：**

```json
{"code": 0, "message": "ok", "data": null}
```

---

## 3. 服务注销

**POST** `/api/v1/services/unregister`

服务关闭时调用。会删除该 IP 的提供方记录；若该服务已无任何提供方，状态自动变为 offline。

**Request Body:**

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| name | string | 是 | 服务唯一标识 |
| server_ip | string | 是 | 提供方服务器 IP |

**示例：**

```bash
curl -X POST http://10.20.240.84:8083/api/v1/services/unregister \
  -H "Content-Type: application/json" \
  -d '{"name": "payment.v1", "server_ip": "10.0.0.1"}'
```

**响应：**

```json
{
  "code": 0,
  "message": "ok",
  "data": { "name": "payment.v1", "status": "offline", "remaining_providers": 0 }
}
```

---

## 4. 错误码

| code | 说明 |
|------|------|
| 0 | 成功 |
| 1001 | 参数缺失（检查必填字段） |
| 1004 | 服务不存在（心跳/注销前需先注册） |
| 5001 | 服务端保存失败 |

---

## 5. 完整接入示例

### Python

```python
import requests, time, threading, atexit

MCP_URL = "http://10.20.240.84:8083/api/v1"  # 内网地址
SERVICE_NAME = "my-service.v1"
SERVER_IP = "10.0.0.3"

# === 1. 启动时注册 ===
requests.post(f"{MCP_URL}/services/register", json={
    "name": SERVICE_NAME,
    "display_name": "我的服务",
    "category": "ai",
    "description": "AI 图片生成微服务",
    "base_url": f"http://{SERVER_IP}:9000",
    "docs_url": f"http://{SERVER_IP}:9000/docs",
    "server_ip": SERVER_IP,
    "project_name": "ai-image",
    "contact": "张三"
})

# === 2. 后台心跳线程 ===
_stop = False
def heartbeat_loop():
    while not _stop:
        try:
            requests.post(f"{MCP_URL}/services/heartbeat", json={
                "name": SERVICE_NAME, "server_ip": SERVER_IP
            }, timeout=5)
        except:
            pass
        time.sleep(30)

threading.Thread(target=heartbeat_loop, daemon=True).start()

# === 3. 关闭时注销 ===
def on_exit():
    global _stop
    _stop = True
    requests.post(f"{MCP_URL}/services/unregister", json={
        "name": SERVICE_NAME, "server_ip": SERVER_IP
    })

atexit.register(on_exit)
```

### PHP (Yii2)

```php
// 启动时注册（放在入口或 bootstrap）
$ch = curl_init('http://10.20.240.84:8083/api/v1/services/register');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => json_encode([
        'name'         => 'my-service.v1',
        'display_name' => '我的服务',
        'category'     => 'payment',
        'base_url'     => 'http://10.0.0.1:8080',
        'server_ip'    => '10.0.0.1',
        'project_name' => 'uni-pay',
    ]),
    CURLOPT_RETURNTRANSFER => true,
]);
curl_exec($ch);
curl_close($ch);

// 心跳（crontab 每 30 秒执行一次）
// * * * * * curl -s -X POST http://10.20.240.84:8083/api/v1/services/heartbeat \
//   -H "Content-Type: application/json" \
//   -d '{"name":"my-service.v1","server_ip":"10.0.0.1"}'
```

### Shell (crontab 心跳)

```bash
# 每分钟发送两次心跳（间隔 30 秒）
* * * * * curl -s -X POST http://10.20.240.84:8083/api/v1/services/heartbeat -H "Content-Type: application/json" -d '{"name":"my-service.v1","server_ip":"10.0.0.1"}'
* * * * * sleep 30 && curl -s -X POST http://10.20.240.84:8083/api/v1/services/heartbeat -H "Content-Type: application/json" -d '{"name":"my-service.v1","server_ip":"10.0.0.1"}'
```
