<template>
  <div>
    <el-row :gutter="16">
      <!-- 左侧：操作面板 -->
      <el-col :span="12">
        <el-card shadow="never">
          <template #header><b>接口测试</b></template>
          <el-tabs v-model="activeTab">
            <!-- 注册 -->
            <el-tab-pane label="服务注册" name="register">
              <el-form label-width="100px" size="default">
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
                <el-form-item label="服务地址" required>
                  <el-input v-model="registerForm.base_url" placeholder="如 http://10.0.0.1:8080" />
                </el-form-item>
                <el-form-item label="文档地址">
                  <el-input v-model="registerForm.docs_url" />
                </el-form-item>
                <el-form-item label="服务器 IP" required>
                  <el-input v-model="registerForm.server_ip" placeholder="如 10.0.0.1" />
                </el-form-item>
                <el-form-item label="项目名" required>
                  <el-input v-model="registerForm.project_name" placeholder="如 uni-pay" />
                </el-form-item>
                <el-form-item label="负责人">
                  <el-input v-model="registerForm.contact" />
                </el-form-item>
                <el-form-item>
                  <el-button type="primary" @click="doRegister" :loading="loading">发送注册请求</el-button>
                  <el-button @click="fillRegisterExample">填入示例</el-button>
                </el-form-item>
              </el-form>
            </el-tab-pane>

            <!-- 心跳 -->
            <el-tab-pane label="心跳上报" name="heartbeat">
              <el-form label-width="100px" size="default">
                <el-form-item label="服务标识" required>
                  <el-input v-model="heartbeatForm.name" placeholder="如 payment.v1" />
                </el-form-item>
                <el-form-item label="服务器 IP" required>
                  <el-input v-model="heartbeatForm.server_ip" placeholder="如 10.0.0.1" />
                </el-form-item>
                <el-form-item>
                  <el-button type="success" @click="doHeartbeat" :loading="loading">发送心跳</el-button>
                </el-form-item>
              </el-form>
            </el-tab-pane>

            <!-- 注销 -->
            <el-tab-pane label="服务注销" name="unregister">
              <el-form label-width="100px" size="default">
                <el-form-item label="服务标识" required>
                  <el-input v-model="unregisterForm.name" placeholder="如 payment.v1" />
                </el-form-item>
                <el-form-item label="服务器 IP" required>
                  <el-input v-model="unregisterForm.server_ip" placeholder="如 10.0.0.1" />
                </el-form-item>
                <el-form-item>
                  <el-button type="danger" @click="doUnregister" :loading="loading">发送注销请求</el-button>
                </el-form-item>
              </el-form>
              <el-alert type="info" :closable="false" style="margin-top:12px;">
                注销将删除该 IP 的提供方记录；若服务已无任何提供方，状态自动置为 offline。
              </el-alert>
            </el-tab-pane>

            <!-- 查询 -->
            <el-tab-pane label="服务查询" name="query">
              <el-form label-width="100px" size="default">
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
              <el-form label-width="100px" size="default">
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
      </el-col>

      <!-- 右侧：请求/响应 -->
      <el-col :span="12">
        <el-card shadow="never" style="margin-bottom:16px;">
          <template #header>
            <div style="display:flex;justify-content:space-between;align-items:center;">
              <b>请求信息</b>
              <el-tag v-if="reqInfo.method" size="small" :type="reqInfo.method === 'GET' ? 'success' : 'primary'">
                {{ reqInfo.method }}
              </el-tag>
            </div>
          </template>
          <div v-if="reqInfo.url" class="code-block">
            <div class="code-label">URL</div>
            <pre>{{ reqInfo.url }}</pre>
          </div>
          <div v-if="reqInfo.body" class="code-block">
            <div class="code-label">Body</div>
            <pre>{{ reqInfo.body }}</pre>
          </div>
          <el-empty v-if="!reqInfo.url" description="发送请求后显示" :image-size="60" />
        </el-card>
        <el-card shadow="never">
          <template #header>
            <div style="display:flex;justify-content:space-between;align-items:center;">
              <b>响应结果</b>
              <el-tag v-if="resInfo.status !== null" size="small"
                :type="resInfo.code === 0 ? 'success' : 'danger'">
                {{ resInfo.code === 0 ? '成功' : '失败' }} (code: {{ resInfo.code }})
              </el-tag>
            </div>
          </template>
          <div v-if="resInfo.raw" class="code-block">
            <pre>{{ resInfo.raw }}</pre>
          </div>
          <el-empty v-if="!resInfo.raw" description="等待响应" :image-size="60" />
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import axios from 'axios'

const loading = ref(false)
const activeTab = ref('register')
const queryMode = ref('list')

const registerForm = reactive({
  name: '', display_name: '', category: '', description: '',
  base_url: '', docs_url: '', server_ip: '', project_name: '', contact: ''
})
const heartbeatForm = reactive({ name: '', server_ip: '' })
const unregisterForm = reactive({ name: '', server_ip: '' })
const queryListForm = reactive({ category: '', keyword: '', status: 'all' })
const queryDetailForm = reactive({ name: '' })
const consumerForm = reactive({ service_name: '', server_ip: '', project_name: '', contact: '' })

const reqInfo = reactive({ method: '', url: '', body: '' })
const resInfo = reactive({ status: null, code: null, raw: '' })

function fillRegisterExample() {
  Object.assign(registerForm, {
    name: 'payment.v1', display_name: '支付服务', category: 'payment',
    description: '内部支付微服务，支持支付宝/微信支付',
    base_url: 'http://10.0.0.1:8080', docs_url: 'http://10.0.0.1:8080/docs',
    server_ip: '10.0.0.1', project_name: 'uni-pay', contact: '张三'
  })
}

async function sendRequest(method, url, body = null) {
  reqInfo.method = method
  reqInfo.url = url
  reqInfo.body = body ? JSON.stringify(body, null, 2) : ''
  resInfo.status = null
  resInfo.code = null
  resInfo.raw = ''
  loading.value = true
  try {
    const config = { method, url, headers: { 'Content-Type': 'application/json' } }
    if (body) config.data = body
    const res = await axios(config)
    resInfo.status = res.status
    resInfo.code = res.data.code ?? null
    resInfo.raw = JSON.stringify(res.data, null, 2)
  } catch (err) {
    resInfo.status = err.response?.status || 0
    resInfo.code = -1
    resInfo.raw = err.response ? JSON.stringify(err.response.data, null, 2) : err.message
  } finally {
    loading.value = false
  }
}

function doRegister() {
  const body = {}
  for (const [k, v] of Object.entries(registerForm)) { if (v) body[k] = v }
  sendRequest('POST', '/api/v1/services/register', body)
}

function doHeartbeat() {
  sendRequest('POST', '/api/v1/services/heartbeat', { ...heartbeatForm })
}

function doUnregister() {
  sendRequest('POST', '/api/v1/services/unregister', { ...unregisterForm })
}

function doQuery() {
  if (queryMode.value === 'detail') {
    sendRequest('GET', `/api/v1/services/${queryDetailForm.name}`)
  } else {
    const params = new URLSearchParams()
    if (queryListForm.category) params.set('category', queryListForm.category)
    if (queryListForm.keyword) params.set('keyword', queryListForm.keyword)
    if (queryListForm.status) params.set('status', queryListForm.status)
    const qs = params.toString()
    sendRequest('GET', `/api/v1/services${qs ? '?' + qs : ''}`)
  }
}

function doConsumer() {
  sendRequest('POST', '/api/v1/consumers/register', { ...consumerForm })
}
</script>

<style scoped>
.code-block {
  background: #f5f7fa;
  border-radius: 6px;
  padding: 12px;
  margin-bottom: 12px;
  overflow-x: auto;
}
.code-block pre {
  margin: 0;
  font-size: 13px;
  line-height: 1.5;
  white-space: pre-wrap;
  word-break: break-all;
  font-family: 'SF Mono', Consolas, 'Liberation Mono', Menlo, monospace;
}
.code-label {
  font-size: 12px;
  color: #909399;
  margin-bottom: 4px;
  font-weight: 600;
}
</style>
