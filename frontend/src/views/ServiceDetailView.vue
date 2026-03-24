<template>
  <div v-loading="loading">
    <el-page-header @back="$router.push('/')" style="margin-bottom: 20px;">
      <template #content>
        <span>{{ detail?.service?.display_name || route.params.name }}</span>
        <el-tag
          v-if="detail?.service"
          :type="detail.service.status === 'online' ? 'success' : 'danger'"
          style="margin-left: 10px;"
          size="small"
        >{{ detail.service.status === 'online' ? '在线' : '离线' }}</el-tag>
      </template>
    </el-page-header>

    <template v-if="detail">
      <!-- 服务基本信息 -->
      <el-card shadow="never" style="margin-bottom: 16px;">
        <template #header><b>服务信息</b></template>
        <el-descriptions :column="2" border>
          <el-descriptions-item label="服务标识">
            <el-tag>{{ detail.service.name }}</el-tag>
          </el-descriptions-item>
          <el-descriptions-item label="展示名">{{ detail.service.display_name }}</el-descriptions-item>
          <el-descriptions-item label="分类">
            <el-tag type="info" size="small">{{ detail.service.category }}</el-tag>
          </el-descriptions-item>
          <el-descriptions-item label="状态">
            <el-badge :type="detail.service.status === 'online' ? 'success' : 'danger'" is-dot style="margin-right:6px" />
            {{ detail.service.status === 'online' ? '在线' : '离线' }}
          </el-descriptions-item>
          <el-descriptions-item label="内网地址" :span="2">
            <a :href="detail.service.internal_url" target="_blank">{{ detail.service.internal_url }}</a>
          </el-descriptions-item>
          <el-descriptions-item label="外网地址" :span="2">
            <a :href="detail.service.external_url" target="_blank">{{ detail.service.external_url }}</a>
          </el-descriptions-item>
          <el-descriptions-item label="文档地址" :span="2">
            <a v-if="detail.service.docs_url" :href="detail.service.docs_url" target="_blank">{{ detail.service.docs_url }}</a>
            <span v-else style="color:#999">-</span>
          </el-descriptions-item>
          <el-descriptions-item label="服务描述" :span="2">{{ detail.service.description || '-' }}</el-descriptions-item>
          <el-descriptions-item label="最后心跳">{{ detail.service.last_heartbeat_at || '-' }}</el-descriptions-item>
          <el-descriptions-item label="注册时间">{{ detail.service.created_at }}</el-descriptions-item>
        </el-descriptions>
      </el-card>

      <!-- 接口文档 -->
      <el-card v-if="detail.service.api_docs && detail.service.api_docs.length" shadow="never" style="margin-bottom: 16px;">
        <template #header>
          <b>接口文档</b>
          <el-tag style="margin-left:8px" size="small">{{ detail.service.api_docs.length }} 个接口</el-tag>
        </template>
        <el-table :data="detail.service.api_docs" stripe style="width:100%">
          <el-table-column label="方法" width="90">
            <template #default="{ row }">
              <el-tag
                :type="methodColor(row.method)"
                size="small"
                style="font-family:monospace;font-weight:600;"
              >{{ row.method }}</el-tag>
            </template>
          </el-table-column>
          <el-table-column prop="path" label="路径" min-width="200">
            <template #default="{ row }">
              <code style="font-size:13px;">{{ row.path }}</code>
            </template>
          </el-table-column>
          <el-table-column prop="description" label="说明" min-width="200" />
          <el-table-column label="请求参数" min-width="200">
            <template #default="{ row }">
              <template v-if="row.request_params && row.request_params.length">
                <div v-for="p in row.request_params" :key="p.name" style="font-size:12px;line-height:1.8;">
                  <code>{{ p.name }}</code>
                  <el-tag v-if="p.required" type="danger" size="small" style="margin-left:4px;transform:scale(0.8);">必填</el-tag>
                  <span style="color:#999;margin-left:4px;">{{ p.type || '' }}</span>
                  <span v-if="p.description" style="color:#666;"> - {{ p.description }}</span>
                </div>
              </template>
              <span v-else style="color:#999">-</span>
            </template>
          </el-table-column>
          <el-table-column label="响应示例" min-width="180">
            <template #default="{ row }">
              <pre v-if="row.response_example" style="margin:0;font-size:12px;white-space:pre-wrap;word-break:break-all;">{{ typeof row.response_example === 'string' ? row.response_example : JSON.stringify(row.response_example, null, 2) }}</pre>
              <span v-else style="color:#999">-</span>
            </template>
          </el-table-column>
        </el-table>
      </el-card>

      <!-- 提供方 -->
      <el-card shadow="never" style="margin-bottom: 16px;">
        <template #header>
          <b>提供方</b>
          <el-tag style="margin-left:8px" size="small">{{ detail.providers.length }}</el-tag>
        </template>
        <el-table :data="detail.providers" stripe>
          <el-table-column prop="internal_ip"  label="内网 IP"    width="160" />
          <el-table-column prop="external_ip"  label="外网 IP"    width="160" />
          <el-table-column prop="project_name" label="项目名"     min-width="160" />
          <el-table-column prop="contact"      label="负责人"     width="120">
            <template #default="{ row }">{{ row.contact || '-' }}</template>
          </el-table-column>
          <el-table-column prop="registered_at" label="注册时间"  width="160" />
        </el-table>
        <el-empty v-if="!detail.providers.length" description="暂无提供方" />
      </el-card>

      <!-- 消费方 -->
      <el-card shadow="never">
        <template #header>
          <b>消费方</b>
          <el-tag style="margin-left:8px" size="small">{{ detail.consumers.length }}</el-tag>
        </template>
        <el-table :data="detail.consumers" stripe>
          <el-table-column prop="server_ip"    label="服务器 IP"  width="160" />
          <el-table-column prop="project_name" label="项目名"     min-width="160" />
          <el-table-column prop="contact"      label="负责人"     width="120">
            <template #default="{ row }">{{ row.contact || '-' }}</template>
          </el-table-column>
          <el-table-column prop="registered_at" label="登记时间"  width="160" />
        </el-table>
        <el-empty v-if="!detail.consumers.length" description="暂无消费方登记" />
      </el-card>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { api } from '../api/index.js'

const route = useRoute()
const loading = ref(false)
const detail = ref(null)

async function load() {
  loading.value = true
  try {
    detail.value = await api.getServiceDetail(route.params.name)
  } finally {
    loading.value = false
  }
}

function methodColor(m) {
  const map = { GET: 'success', POST: 'primary', PUT: 'warning', DELETE: 'danger' }
  return map[(m || '').toUpperCase()] || 'info'
}

onMounted(load)
</script>
