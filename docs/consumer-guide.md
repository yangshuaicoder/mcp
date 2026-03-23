# MCP 服务使用方接入文档

> 版本：v1.1 | 日期：2026-03-23

本文档面向 **业务系统开发者**（需要接入内部微服务的消费方），指导如何查找可用服务并登记使用关系。

## MCP 地址

| 环境 | 地址 |
|------|------|
| **外网** | `http://43.142.159.201:8083` |
| **内网** | `http://10.20.240.84:8083` |
| **管理后台** | `http://43.142.159.201:8083`（浏览器访问） |

## 接口规范

- Base Path: `/api/v1`
- 请求格式: `Content-Type: application/json`
- 响应格式: 统一 JSON `{"code": 0, "message": "ok", "data": {}}`
- code=0 表示成功，非 0 表示错误
- 无需鉴权（v1.0）

---

## 接入流程

```
在 MCP 查询可用服务 → 找到目标服务 → 获取服务地址和文档 → 接入开发 → 登记消费关系
```

你也可以直接访问 **管理后台** `http://43.142.159.201:8083` 在网页上浏览所有服务。

---

## 1. 查询服务列表

**GET** `/api/v1/services`

查找当前可用的微服务。

**Query Params:**

| 参数 | 类型 | 必填 | 默认值 | 说明 |
|------|------|------|--------|------|
| category | string | 否 | - | 按分类过滤，如 `payment`、`ai`、`logistics` |
| keyword | string | 否 | - | 关键词搜索（匹配服务标识、展示名、描述） |
| status | string | 否 | `online` | `online` 仅在线 / `offline` 仅离线 / `all` 全部 |
| page | int | 否 | 1 | 页码 |
| page_size | int | 否 | 20 | 每页条数（最大 100） |

**示例：**

```bash
# 查询所有在线服务
curl "http://10.20.240.84:8083/api/v1/services"

# 按分类查询
curl "http://10.20.240.84:8083/api/v1/services?category=payment"

# 关键词搜索
curl "http://10.20.240.84:8083/api/v1/services?keyword=支付&status=all"
```

**响应：**

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
        "description": "内部支付微服务，支持支付宝/微信支付",
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

**关键字段说明：**

| 字段 | 你需要关注的 |
|------|-------------|
| `name` | 服务唯一标识，登记消费时需要用到 |
| `base_url` | 服务根地址，你的代码直接调用这个地址 |
| `docs_url` | 服务接口文档，查看该服务提供了哪些 API |
| `status` | `online` 表示可用，`offline` 表示不可用 |
| `category` | 服务分类，方便按类型筛选 |

---

## 2. 查询服务详情

**GET** `/api/v1/services/{name}`

获取某个服务的详细信息，包括提供方（谁在提供）和消费方（谁在使用）。

**示例：**

```bash
curl "http://10.20.240.84:8083/api/v1/services/payment.v1"
```

**响应：**

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
        "internal_ip": "10.0.0.1",
        "external_ip": "43.142.159.201",
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

## 3. 登记消费关系

**POST** `/api/v1/consumers/register`

当你的项目接入了某个微服务后，请调用此接口登记。这有助于：
- 追踪谁在使用哪些服务
- 服务下线前评估影响范围
- 方便服务提供方联系你

**重复登记不报错**，静默忽略。

**Request Body:**

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| service_name | string | 是 | 要使用的服务标识（从查询结果的 `name` 字段获取） |
| server_ip | string | 是 | 你的服务器 IP |
| project_name | string | 是 | 你的项目名 |
| contact | string | 否 | 负责人 |

**示例：**

```bash
curl -X POST http://10.20.240.84:8083/api/v1/consumers/register \
  -H "Content-Type: application/json" \
  -d '{
    "service_name": "payment.v1",
    "server_ip": "10.0.0.2",
    "project_name": "order-service",
    "contact": "李四"
  }'
```

**响应：**

```json
{"code": 0, "message": "ok", "data": null}
```

---

## 4. 错误码

| code | 说明 |
|------|------|
| 0 | 成功 |
| 1001 | 参数缺失（检查必填字段） |
| 1004 | 服务不存在（确认 service_name 是否正确） |

---

## 5. 完整接入示例

### Python

```python
import requests

MCP_URL = "http://10.20.240.84:8083/api/v1"  # 内网地址

# === 1. 查找需要的服务 ===
res = requests.get(f"{MCP_URL}/services", params={"category": "payment"})
services = res.json()["data"]["list"]

for svc in services:
    print(f"{svc['name']} - {svc['display_name']} - {svc['base_url']}")

# === 2. 查看详情（获取文档地址和负责人） ===
detail = requests.get(f"{MCP_URL}/services/payment.v1").json()["data"]
print(f"文档地址: {detail['service']['docs_url']}")
print(f"负责人: {detail['providers'][0]['contact']}")

# === 3. 登记消费关系 ===
requests.post(f"{MCP_URL}/consumers/register", json={
    "service_name": "payment.v1",
    "server_ip": "10.0.0.5",
    "project_name": "my-biz-app",
    "contact": "李四"
})
```

### PHP

```php
// 查询可用服务
$services = json_decode(file_get_contents(
    'http://10.20.240.84:8083/api/v1/services?category=payment'
), true);

foreach ($services['data']['list'] as $svc) {
    echo "{$svc['name']} - {$svc['base_url']}\n";
}

// 登记消费
$ch = curl_init('http://10.20.240.84:8083/api/v1/consumers/register');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => json_encode([
        'service_name' => 'payment.v1',
        'server_ip'    => '10.0.0.5',
        'project_name' => 'my-biz-app',
        'contact'      => '李四',
    ]),
    CURLOPT_RETURNTRANSFER => true,
]);
curl_exec($ch);
curl_close($ch);
```
