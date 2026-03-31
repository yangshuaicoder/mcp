<template>
  <div>
    <!-- 页面标题 -->
    <div class="page-header">
      <div>
        <h1 class="page-title">接口测试</h1>
        <p class="page-desc">直接向 MCP 接口发送请求并查看响应</p>
      </div>
    </div>

    <div class="test-layout">
      <!-- 左侧：操作面板 -->
      <div class="panel-left">
        <el-card shadow="never">
          <el-tabs v-model="activeTab">
            <!-- 注册 -->
            <el-tab-pane label="服务注册" name="register">
              <el-form label-width="90px" size="default" class="tab-form">
                <el-form-item label="服务标识" required>
                  <el-input v-model="registerForm.name" placeholder="如 payment.v1" />
                </el-form-item>
                <el-form-item label="展示名" required>
                  <el-input v-model="registerForm.display_name" placeholder="如 支付服务" />
                </el-form-item>
                <el-form-item label="分类">
                  <el-input v-model="registerForm.category" placeholder="如 payment" />
                </el-form-item>
                <el-form-item label="描述">
                  <el-input v-model="registerForm.description" type="textarea" :rows="2" />
                </el-form-item>
                <el-form-item label="内网地址" required>
                  <el-input v-model="registerForm.internal_url" placeholder="http://10.0.0.1:8080" />
                </el-form-item>
                <el-form-item label="外网地址" required>
                  <el-input v-model="registerForm.external_url" placeholder="http://43.142.159.201:8080" />
                </el-form-item>
                <el-form-item label="文档地址" required>
                  <el-input v-model="registerForm.docs_url" placeholder="http://10.0.0.1:8080/docs" />
                </el-form-item>
                <el-form-item label="内网 IP" required>
                  <el-input v-model="registerForm.internal_ip" placeholder="10.0.0.1" />
                </el-form-item>
                <el-form-item label="外网 IP" required>
                  <el-input v-model="registerForm.external_ip" placeholder="43.142.159.201" />
                </el-form-item>
                <el-form-item label="项目名" required>
                  <el-input v-model="registerForm.project_name" placeholder="如 uni-pay" />
                </el-form-item>
                <el-form-item label="负责人">
                  <el-input v-model="registerForm.contact" />
                </el-form-item>

                <div class="divider-label">接口文档（必填）</div>
                <div v-for="(doc, idx) in apiDocsList" :key="idx" class="api-doc-item">
                  <div class="api-doc-header">
                    <span class="api-doc-idx"># {{ idx + 1 }}</span>
                    <el-button type="danger" size="small" text @click="removeApiDoc(idx)" :disabled="apiDocsList.length <= 1">删除</el-button>
                  </div>
                  <el-row :gutter="8">
                    <el-col :span="6">
                      <el-select v-model="doc.method" placeholder="方法" style="width:100%">
                        <el-option label="GET"    value="GET" />
                        <el-option label="POST"   value="POST" />
                        <el-option label="PUT"    value="PUT" />
                        <el-option label="DELETE" value="DELETE" />
                      </el-select>
                    </el-col>
                    <el-col :span="8">
                      <el-input v-model="doc.path" placeholder="如 /api/v1/pay" />
                    </el-col>
                    <el-col :span="10">
                      <el-input v-model="doc.description" placeholder="接口说明" />
                    </el-col>
                  </el-row>
                  <el-row :gutter="8" style="margin-top:6px;">
                    <el-col :span="12">
                      <el-input v-model="doc.request_params_json" type="textarea" :rows="2"
                        placeholder='请求参数 JSON，如 [{"name":"order_id","type":"string","required":true}]' />
                    </el-col>
                    <el-col :span="12">
                      <el-input v-model="doc.response_example_json" type="textarea" :rows="2"
                        placeholder='响应示例 JSON，如 {"code":0,"data":{}}' />
                    </el-col>
                  </el-row>
                </div>
                <el-button type="primary" text @click="addApiDoc" style="margin-bottom:12px;">+ 添加接口</el-button>

                <el-form-item>
                  <el-button type="primary" @click="doRegister" :loading="loading">发送注册请求</el-button>
                  <el-button @click="fillRegisterExample">填入示例</el-button>
                </el-form-item>
              </el-form>
            </el-tab-pane>

            <!-- 心跳 -->
            <el-tab-pane label="心跳上报" name="heartbeat">
              <el-form label-width="90px" size="default" class="tab-form">
                <el-form-item label="服务标识" required>
                  <el-input v-model="heartbeatForm.name" placeholder="如 payment.v1" />
                </el-form-item>
                <el-form-item label="内网 IP" required>
                  <el-input v-model="heartbeatForm.internal_ip" placeholder="如 10.0.0.1" />
                </el-form-item>
                <el-form-item>
                  <el-button type="success" @click="doHeartbeat" :loading="loading">发送心跳</el-button>
                </el-form-item>
              </el-form>
            </el-tab-pane>

            <!-- 注销 -->
            <el-tab-pane label="服务注销" name="unregister">
              <el-form label-width="90px" size="default" class="tab-form">
                <el-form-item label="服务标识" required>
                  <el-input v-model="unregisterForm.name" placeholder="如 payment.v1" />
                </el-form-item>
                <el-form-item label="内网 IP" required>
                  <el-input v-model="unregisterForm.internal_ip" placeholder="如 10.0.0.1" />
                </el-form-item>
                <el-form-item>
                  <el-button type="danger" @click="doUnregister" :loading="loading">发送注销请求</el-button>
                </el-form-item>
              </el-form>
              <div class="info-tip">
                注销将删除该 IP 的提供方记录；若服务已无任何提供方，状态自动置为 offline。
              </div>
            </el-tab-pane>

            <!-- 查询 -->
            <el-tab-pane label="服务查询" name="query">
              <el-form label-width="90px" size="default" class="tab-form">
                <el-form-item label="查询方式">
                  <el-radio-group v-model="queryMode">
                    <el-radio value="list">列表查询</el-radio>
                    <el-radio value="detail">详情查询</el-radio>
                  </el-radio-group>
                </el-form-item>
                <template v-if="queryMode === 'list'">
                  <el-form-item label="分类">
                    <el-input v-model="queryListForm.category" placeholder="可选" />
                  </el-form-item>
                  <el-form-item label="关键词">
                    <el-input v-model="queryListForm.keyword" placeholder="可选" />
                  </el-form-item>
                  <el-form-item label="状态">
                    <el-select v-model="queryListForm.status">
                      <el-option label="在线" value="online" />
                      <el-option label="离线" value="offline" />
                      <el-option label="全部" value="all" />
                    </el-select>
                  </el-form-item>
                </template>
                <template v-else>
                  <el-form-item label="服务标识" required>
                    <el-input v-model="queryDetailForm.name" placeholder="如 payment.v1" />
                  </el-form-item>
                </template>
                <el-form-item>
                  <el-button type="primary" @click="doQuery" :loading="loading">发送查询</el-button>
                </el-form-item>
              </el-form>
            </el-tab-pane>

            <!-- 消费方登记 -->
            <el-tab-pane label="消费方登记" name="consumer">
              <el-form label-width="90px" size="default" class="tab-form">
                <el-form-item label="服务标识" required>
                  <el-input v-model="consumerForm.service_name" placeholder="如 payment.v1" />
                </el-form-item>
                <el-form-item label="服务器 IP" required>
                  <el-input v-model="consumerForm.server_ip" placeholder="消费方服务器 IP" />
                </el-form-item>
                <el-form-item label="项目名" required>
                  <el-input v-model="consumerForm.project_name" placeholder="消费方项目名" />
                </el-form-item>
                <el-form-item label="负责人">
                  <el-input v-model="consumerForm.contact" />
                </el-form-item>
                <el-form-item>
                  <el-button type="warning" @click="doConsumer" :loading="loading">发送登记</el-button>
                </el-form-item>
              </el-form>
            </el-tab-pane>
          </el-tabs>
        </el-card>
      </div>

      <!-- 右侧：请求/响应 -->
      <div class="panel-right">
        <!-- 请求信息 -->
        <div class="console-card">
          <div class="console-header">
            <span class="console-title">请求信息</span>
            <span v-if="reqInfo.method" class="method-pill" :class="`method-${reqInfo.method.toLowerCase()}`">
              {{ reqInfo.method }}
            </span>
          </div>
          <div v-if="reqInfo.url" class="console-body">
            <div class="console-block">
              <div class="console-label">URL</div>
              <pre class="console-pre">{{ reqInfo.url }}</pre>
            </div>
            <div v-if="reqInfo.body" class="console-block">
              <div class="console-label">Body</div>
              <pre class="console-pre">{{ reqInfo.body }}</pre>
            </div>
          </div>
          <div v-else class="console-empty">发送请求后显示</div>
        </div>

        <!-- 响应结果 -->
        <div class="console-card">
          <div class="console-header">
            <span class="console-title">响应结果</span>
            <span v-if="resInfo.status !== null"
              class="res-status-pill"
              :class="resInfo.code === 0 ? 'res-ok' : 'res-err'"
            >
              {{ resInfo.code === 0 ? '成功' : '失败' }} · code {{ resInfo.code }}
            </span>
          </div>
          <div v-if="resInfo.raw" class="console-body">
            <pre class="console-pre">{{ resInfo.raw }}</pre>
          </div>
          <div v-else class="console-empty">等待响应</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import axios from 'axios'

const loading    = ref(false)
const activeTab  = ref('register')
const queryMode  = ref('list')

const registerForm = reactive({
  name: '', display_name: '', category: '', description: '',
  internal_url: '', external_url: '', docs_url: '',
  internal_ip: '', external_ip: '', project_name: '', contact: ''
})
const apiDocsList = reactive([
  { method: 'POST', path: '', description: '', request_params_json: '', response_example_json: '' }
])

function addApiDoc() {
  apiDocsList.push({ method: 'POST', path: '', description: '', request_params_json: '', response_example_json: '' })
}
function removeApiDoc(idx) {
  if (apiDocsList.length > 1) apiDocsList.splice(idx, 1)
}
function buildApiDocs() {
  return apiDocsList.map(d => {
    const item = { method: d.method, path: d.path, description: d.description }
    if (d.request_params_json) {
      try { item.request_params = JSON.parse(d.request_params_json) } catch (e) { /* skip */ }
    }
    if (d.response_example_json) {
      try { item.response_example = JSON.parse(d.response_example_json) } catch (e) { item.response_example = d.response_example_json }
    }
    return item
  })
}

const heartbeatForm   = reactive({ name: '', internal_ip: '' })
const unregisterForm  = reactive({ name: '', internal_ip: '' })
const queryListForm   = reactive({ category: '', keyword: '', status: 'all' })
const queryDetailForm = reactive({ name: '' })
const consumerForm    = reactive({ service_name: '', server_ip: '', project_name: '', contact: '' })

const reqInfo = reactive({ method: '', url: '', body: '' })
const resInfo = reactive({ status: null, code: null, raw: '' })

function fillRegisterExample() {
  Object.assign(registerForm, {
    name: 'payment.v1', display_name: '支付服务', category: 'payment',
    description: '内部支付微服务，支持支付宝/微信支付',
    internal_url: 'http://10.0.0.1:8080', external_url: 'http://1.2.3.4:8080',
    docs_url: 'http://10.0.0.1:8080/docs',
    internal_ip: '10.0.0.1', external_ip: '1.2.3.4', project_name: 'uni-pay', contact: '张三'
  })
  apiDocsList.splice(0, apiDocsList.length,
    {
      method: 'POST', path: '/api/v1/pay/create', description: '创建支付订单',
      request_params_json: JSON.stringify([
        { name: 'order_id', type: 'string', required: true, description: '订单号' },
        { name: 'amount',   type: 'number', required: true, description: '金额（分）' },
        { name: 'channel',  type: 'string', required: true, description: '支付渠道：alipay/wechat' }
      ], null, 2),
      response_example_json: JSON.stringify({ code: 0, data: { pay_url: 'https://...' } }, null, 2)
    },
    {
      method: 'GET', path: '/api/v1/pay/query', description: '查询支付状态',
      request_params_json: JSON.stringify([
        { name: 'order_id', type: 'string', required: true, description: '订单号' }
      ], null, 2),
      response_example_json: JSON.stringify({ code: 0, data: { status: 'paid' } }, null, 2)
    }
  )
}

async function sendRequest(method, url, body = null) {
  reqInfo.method = method
  reqInfo.url    = url
  reqInfo.body   = body ? JSON.stringify(body, null, 2) : ''
  resInfo.status = null
  resInfo.code   = null
  resInfo.raw    = ''
  loading.value  = true
  try {
    const config = { method, url, headers: { 'Content-Type': 'application/json' } }
    if (body) config.data = body
    const res = await axios(config)
    resInfo.status = res.status
    resInfo.code   = res.data.code ?? null
    resInfo.raw    = JSON.stringify(res.data, null, 2)
  } catch (err) {
    resInfo.status = err.response?.status || 0
    resInfo.code   = -1
    resInfo.raw    = err.response ? JSON.stringify(err.response.data, null, 2) : err.message
  } finally {
    loading.value = false
  }
}

async function doRegister() {
  const body = {}
  for (const [k, v] of Object.entries(registerForm)) { if (v) body[k] = v }
  body.api_docs = buildApiDocs()
  await sendRequest('POST', '/api/v1/services/register', body)
}
async function doHeartbeat()  { await sendRequest('POST', '/api/v1/services/heartbeat',  { ...heartbeatForm }) }
async function doUnregister() { await sendRequest('POST', '/api/v1/services/unregister', { ...unregisterForm }) }
async function doQuery() {
  if (queryMode.value === 'detail') {
    await sendRequest('GET', `/api/v1/services/${queryDetailForm.name}`)
  } else {
    const params = new URLSearchParams()
    if (queryListForm.category) params.set('category', queryListForm.category)
    if (queryListForm.keyword)  params.set('keyword',  queryListForm.keyword)
    if (queryListForm.status)   params.set('status',   queryListForm.status)
    const qs = params.toString()
    await sendRequest('GET', `/api/v1/services${qs ? '?' + qs : ''}`)
  }
}
async function doConsumer() { await sendRequest('POST', '/api/v1/consumers/register', { ...consumerForm }) }
</script>

<style scoped>
.page-header {
  margin-bottom: 24px;
}
.page-title {
  font-size: 22px;
  font-weight: 700;
  color: #111;
  letter-spacing: -0.02em;
}
.page-desc {
  font-size: 13px;
  color: var(--gray-medium);
  margin-top: 3px;
}

/* 双栏布局 */
.test-layout {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  align-items: start;
}
.panel-left, .panel-right {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.tab-form { padding-top: 4px; }

/* 分隔线 */
.divider-label {
  font-size: 12px;
  font-weight: 600;
  color: #888;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin: 8px 0 10px;
  padding-bottom: 8px;
  border-bottom: 1px solid rgba(0,0,0,0.06);
}

/* 接口文档项 */
.api-doc-item {
  background: #fafaf8;
  border: 1px solid rgba(0,0,0,0.06);
  border-radius: 10px;
  padding: 10px 12px;
  margin-bottom: 10px;
}
.api-doc-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}
.api-doc-idx {
  font-size: 12px;
  font-weight: 600;
  color: var(--gray-medium);
  font-family: 'Menlo', 'Consolas', monospace;
}

/* 信息提示 */
.info-tip {
  margin-top: 10px;
  padding: 10px 14px;
  background: rgba(255,107,53,0.05);
  border: 1px solid rgba(255,107,53,0.15);
  border-radius: 10px;
  font-size: 13px;
  color: #666;
  line-height: 1.6;
}

/* 控制台卡片 */
.console-card {
  background: #0f1117;
  border-radius: 14px;
  overflow: hidden;
  border: 1px solid rgba(255,255,255,0.06);
}
.console-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 16px;
  border-bottom: 1px solid rgba(255,255,255,0.06);
}
.console-title {
  font-size: 12px;
  font-weight: 600;
  color: rgba(255,255,255,0.5);
  text-transform: uppercase;
  letter-spacing: 0.08em;
}
.console-body {
  padding: 14px 16px;
}
.console-block {
  margin-bottom: 12px;
}
.console-block:last-child { margin-bottom: 0; }
.console-label {
  font-size: 10px;
  font-weight: 600;
  color: rgba(255,255,255,0.25);
  text-transform: uppercase;
  letter-spacing: 0.08em;
  margin-bottom: 5px;
}
.console-pre {
  margin: 0;
  font-size: 12.5px;
  line-height: 1.6;
  white-space: pre-wrap;
  word-break: break-all;
  font-family: 'Menlo', 'SF Mono', 'Consolas', monospace;
  color: #e2e8f0;
}
.console-empty {
  padding: 28px 16px;
  font-size: 12px;
  color: rgba(255,255,255,0.2);
  text-align: center;
  font-family: 'Menlo', 'Consolas', monospace;
}

/* 方法标签 */
.method-pill {
  display: inline-flex;
  align-items: center;
  padding: 2px 8px;
  border-radius: 6px;
  font-size: 10px;
  font-weight: 700;
  font-family: 'Menlo', 'Consolas', monospace;
  letter-spacing: 0.05em;
}
.method-get    { background: rgba(22,163,74,0.15);  color: #4ade80; }
.method-post   { background: rgba(255,107,53,0.15); color: var(--orange-light); }
.method-put    { background: rgba(217,119,6,0.15);  color: #fbbf24; }
.method-delete { background: rgba(220,38,38,0.15);  color: #f87171; }

/* 响应状态 */
.res-status-pill {
  display: inline-flex;
  align-items: center;
  padding: 2px 9px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 500;
}
.res-ok  { background: rgba(22,163,74,0.15);  color: #4ade80; }
.res-err { background: rgba(220,38,38,0.15);  color: #f87171; }
</style>
