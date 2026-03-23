# MCP 管理后台 -- API 接口文档

> 版本：v1.0 | 日期：2026-03-20

## 服务地址

| 环境 | 地址 |
|------|------|
| **外网** | `http://43.142.159.201:8083` |
| **内网** | `http://10.20.240.84:8083` |

## 接口规范

- Base Path: `/api/v1`
- 请求格式: `Content-Type: application/json`
- 响应格式: 统一 JSON

```json
{
  "code": 0,        // 0=成功，非0=错误
  "message": "ok",
  "data": {}
}
```

- 无需鉴权（v1.0，内网使用）

---

## 一、服务管理接口

### 1.1 服务注册

**POST** `/api/v1/services/register`

注册或更新一个微服务。name 相同则更新，不同则新建。

**Request Body:**

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| name | string | 是 | 服务唯一标识，如 `payment.v1` |
| display_name | string | 是 | 展示名，如 `支付服务` |
| category | string | 否 | 分类（自报），默认 `other` |
| description | string | 否 | 服务描述 |
| base_url | string | 是 | 服务根地址 |
| docs_url | string | 否 | 文档地址 |
| server_ip | string | 是 | 提供方服务器 IP |
| project_name | string | 是 | 提供方项目名 |
| contact | string | 否 | 负责人 |

**示例请求:**

```bash
# 外网
curl -X POST http://43.142.159.201:8083/api/v1/services/register \
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

# 内网
curl -X POST http://10.20.240.84:8083/api/v1/services/register \
  -H "Content-Type: application/json" \
  -d '{ ... }'
```

**响应:**

```json
{
  "code": 0,
  "message": "ok",
  "data": {
    "id": 1,
    "name": "payment.v1",
    "status": "online"
  }
}
```

---

### 1.2 心跳上报

**POST** `/api/v1/services/heartbeat`

微服务定时发送心跳（建议间隔 30~60 秒）。超过 3 分钟未收到心跳，系统自动将服务状态置为 offline。

**Request Body:**

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| name | string | 是 | 服务唯一标识 |
| server_ip | string | 是 | 提供方服务器 IP |

**示例请求:**

```bash
# 外网
curl -X POST http://43.142.159.201:8083/api/v1/services/heartbeat \
  -H "Content-Type: application/json" \
  -d '{"name": "payment.v1", "server_ip": "10.0.0.1"}'

# 内网
curl -X POST http://10.20.240.84:8083/api/v1/services/heartbeat \
  -H "Content-Type: application/json" \
  -d '{"name": "payment.v1", "server_ip": "10.0.0.1"}'
```

**响应:**

```json
{"code": 0, "message": "ok", "data": null}
```

---

### 1.3 服务注销

**POST** `/api/v1/services/unregister`

注销指定 IP 的提供方记录。若该服务已无任何提供方，状态自动置为 offline。

**Request Body:**

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| name | string | 是 | 服务唯一标识 |
| server_ip | string | 是 | 提供方服务器 IP |

**示例请求:**

```bash
# 外网
curl -X POST http://43.142.159.201:8083/api/v1/services/unregister \
  -H "Content-Type: application/json" \
  -d '{"name": "payment.v1", "server_ip": "10.0.0.1"}'

# 内网
curl -X POST http://10.20.240.84:8083/api/v1/services/unregister \
  -H "Content-Type: application/json" \
  -d '{"name": "payment.v1", "server_ip": "10.0.0.1"}'
```

**响应:**

```json
{
  "code": 0,
  "message": "ok",
  "data": {
    "name": "payment.v1",
    "status": "offline",
    "remaining_providers": 0
  }
}
```

---

### 1.4 查询服务列表

**GET** `/api/v1/services`

**Query Params:**

| 参数 | 类型 | 必填 | 默认值 | 说明 |
|------|------|------|--------|------|
| category | string | 否 | - | 按分类过滤 |
| keyword | string | 否 | - | 搜索 name/display_name/description |
| status | string | 否 | `online` | `online` / `offline` / `all` |
| page | int | 否 | 1 | 页码 |
| page_size | int | 否 | 20 | 每页条数（最大100） |

**示例请求:**

```bash
# 外网 - 查询所有在线服务
curl "http://43.142.159.201:8083/api/v1/services"

# 外网 - 查询所有服务（含离线）
curl "http://43.142.159.201:8083/api/v1/services?status=all"

# 内网 - 按分类过滤
curl "http://10.20.240.84:8083/api/v1/services?category=payment"
```

**响应:**

```json
{
  "code": 0,
  "message": "ok",
  "data": {
    "list": [
      {
        "id": 1,
        "name": "payment.v1",
        "display_name": "支付服务",
        "category": "payment",
        "description": "内部支付微服务",
        "base_url": "http://10.0.0.1:8080",
        "docs_url": "http://10.0.0.1:8080/docs",
        "status": "online",
        "last_heartbeat_at": "2026-03-20 18:00:00",
        "created_at": "2026-03-20 10:00:00",
        "updated_at": "2026-03-20 18:00:00"
      }
    ],
    "total": 1,
    "page": 1,
    "page_size": 20
  }
}
```

---

### 1.5 查询服务详情

**GET** `/api/v1/services/{name}`

返回服务信息、提供方列表、消费方列表。

**示例请求:**

```bash
# 外网
curl "http://43.142.159.201:8083/api/v1/services/payment.v1"

# 内网
curl "http://10.20.240.84:8083/api/v1/services/payment.v1"
```

**响应:**

```json
{
  "code": 0,
  "message": "ok",
  "data": {
    "service": {
      "id": 1,
      "name": "payment.v1",
      "display_name": "支付服务",
      "category": "payment",
      "description": "内部支付微服务",
      "base_url": "http://10.0.0.1:8080",
      "docs_url": "http://10.0.0.1:8080/docs",
      "status": "online",
      "last_heartbeat_at": "2026-03-20 18:00:00",
      "created_at": "2026-03-20 10:00:00",
      "updated_at": "2026-03-20 18:00:00"
    },
    "providers": [
      {
        "id": 1,
        "server_ip": "10.0.0.1",
        "project_name": "uni-pay",
        "contact": "张三",
        "registered_at": "2026-03-20 10:00:00"
      }
    ],
    "consumers": [
      {
        "id": 1,
        "server_ip": "10.0.0.2",
        "project_name": "order-service",
        "contact": "李四",
        "registered_at": "2026-03-20 10:30:00"
      }
    ]
  }
}
```

---

## 二、消费方接口

### 2.1 消费方登记

**POST** `/api/v1/consumers/register`

业务系统登记「我在使用 XX 服务」。重复登记不报错，静默忽略。

**Request Body:**

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| service_name | string | 是 | 要使用的服务标识 |
| server_ip | string | 是 | 消费方服务器 IP |
| project_name | string | 是 | 消费方项目名 |
| contact | string | 否 | 负责人 |

**示例请求:**

```bash
# 外网
curl -X POST http://43.142.159.201:8083/api/v1/consumers/register \
  -H "Content-Type: application/json" \
  -d '{
    "service_name": "payment.v1",
    "server_ip": "10.0.0.2",
    "project_name": "order-service",
    "contact": "李四"
  }'

# 内网
curl -X POST http://10.20.240.84:8083/api/v1/consumers/register \
  -H "Content-Type: application/json" \
  -d '{ ... }'
```

**响应:**

```json
{"code": 0, "message": "ok", "data": null}
```

---

## 三、错误码

| code | 说明 |
|------|------|
| 0 | 成功 |
| 1001 | 参数缺失 |
| 1004 | 服务不存在 |
| 5001 | 服务端保存失败 |

---

## 四、接入指南

### 微服务提供方

服务启动时调用 **注册接口**，之后定时发送 **心跳**（建议 30s），服务关闭时调用 **注销接口**：

```python
# Python 示例
import requests, time, threading

MCP_URL = "http://10.20.240.84:8083/api/v1"  # 内网地址

# 注册
requests.post(f"{MCP_URL}/services/register", json={
    "name": "my-service.v1",
    "display_name": "我的服务",
    "category": "ai",
    "base_url": "http://10.0.0.3:9000",
    "server_ip": "10.0.0.3",
    "project_name": "my-project"
})

# 心跳（后台线程）
def heartbeat_loop():
    while True:
        requests.post(f"{MCP_URL}/services/heartbeat", json={
            "name": "my-service.v1", "server_ip": "10.0.0.3"
        })
        time.sleep(30)
threading.Thread(target=heartbeat_loop, daemon=True).start()
```

### 业务消费方

查询可用服务并登记使用关系：

```python
# 查询在线服务
res = requests.get(f"{MCP_URL}/services?category=ai")

# 登记消费
requests.post(f"{MCP_URL}/consumers/register", json={
    "service_name": "my-service.v1",
    "server_ip": "10.0.0.5",
    "project_name": "biz-app"
})
```
