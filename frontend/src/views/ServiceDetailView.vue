<template>
  <div v-loading="loading">
    <!-- 页面头部 -->
    <div class="page-header">
      <button class="back-btn" @click="$router.push('/')">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
        返回
      </button>
      <div class="header-main" v-if="detail?.service">
        <h1 class="page-title">{{ detail.service.display_name }}</h1>
        <div class="header-meta">
          <code class="service-id">{{ detail.service.name }}</code>
          <span class="status-badge" :class="detail.service.status === 'online' ? 'status-online' : 'status-offline'">
            {{ detail.service.status === 'online' ? '在线' : '离线' }}
          </span>
          <el-tag size="small" type="info" v-if="detail.service.category">{{ detail.service.category }}</el-tag>
        </div>
      </div>
    </div>

    <template v-if="detail">
      <!-- 服务基本信息 -->
      <el-card shadow="never" class="section-card">
        <template #header>
          <div class="card-header-row">
            <span class="section-label">服务信息</span>
          </div>
        </template>
        <div class="info-grid">
          <div class="info-item">
            <div class="info-key">内网地址</div>
            <a :href="detail.service.internal_url" target="_blank" class="info-link">{{ detail.service.internal_url || '-' }}</a>
          </div>
          <div class="info-item">
            <div class="info-key">外网地址</div>
            <a :href="detail.service.external_url" target="_blank" class="info-link">{{ detail.service.external_url || '-' }}</a>
          </div>
          <div class="info-item">
            <div class="info-key">文档地址</div>
            <a v-if="detail.service.docs_url" :href="detail.service.docs_url" target="_blank" class="info-link">{{ detail.service.docs_url }}</a>
            <span v-else class="info-empty">-</span>
          </div>
          <div class="info-item">
            <div class="info-key">最后心跳</div>
            <span class="info-value">{{ detail.service.last_heartbeat_at || '-' }}</span>
          </div>
          <div class="info-item">
            <div class="info-key">注册时间</div>
            <span class="info-value">{{ detail.service.created_at }}</span>
          </div>
          <div class="info-item info-item--full" v-if="detail.service.description">
            <div class="info-key">服务描述</div>
            <span class="info-value">{{ detail.service.description }}</span>
          </div>
        </div>
      </el-card>

      <!-- 接口文档 -->
      <el-card
        v-if="detail.service.api_docs && detail.service.api_docs.length"
        shadow="never"
        class="section-card"
      >
        <template #header>
          <div class="card-header-row">
            <span class="section-label">接口文档</span>
            <span class="count-badge">{{ detail.service.api_docs.length }} 个接口</span>
          </div>
        </template>
        <el-table :data="detail.service.api_docs" stripe style="width:100%">
          <el-table-column label="方法" width="90">
            <template #default="{ row }">
              <span class="method-badge" :class="`method-${(row.method||'').toLowerCase()}`">{{ row.method }}</span>
            </template>
          </el-table-column>
          <el-table-column prop="path" label="路径" min-width="200">
            <template #default="{ row }">
              <code class="path-code">{{ row.path }}</code>
            </template>
          </el-table-column>
          <el-table-column prop="description" label="说明" min-width="200" />
          <el-table-column label="请求参数" min-width="200">
            <template #default="{ row }">
              <template v-if="row.request_params && row.request_params.length">
                <div v-for="p in row.request_params" :key="p.name" class="param-row">
                  <code class="param-name">{{ p.name }}</code>
                  <span class="param-required" v-if="p.required">必填</span>
                  <span class="param-type" v-if="p.type">{{ p.type }}</span>
                  <span class="param-desc" v-if="p.description">— {{ p.description }}</span>
                </div>
              </template>
              <span v-else class="info-empty">-</span>
            </template>
          </el-table-column>
          <el-table-column label="响应示例" min-width="180">
            <template #default="{ row }">
              <pre v-if="row.response_example" class="response-pre">{{ typeof row.response_example === 'string' ? row.response_example : JSON.stringify(row.response_example, null, 2) }}</pre>
              <span v-else class="info-empty">-</span>
            </template>
          </el-table-column>
        </el-table>
      </el-card>

      <!-- 提供方 -->
      <el-card shadow="never" class="section-card">
        <template #header>
          <div class="card-header-row">
            <span class="section-label">提供方</span>
            <span class="count-badge">{{ detail.providers.length }}</span>
          </div>
        </template>
        <el-table v-if="detail.providers.length" :data="detail.providers" stripe>
          <el-table-column prop="internal_ip"   label="内网 IP"   width="160" />
          <el-table-column prop="external_ip"   label="外网 IP"   width="160" />
          <el-table-column prop="project_name"  label="项目名"    min-width="160" />
          <el-table-column prop="contact"       label="负责人"    width="120">
            <template #default="{ row }">{{ row.contact || '-' }}</template>
          </el-table-column>
          <el-table-column prop="registered_at" label="注册时间"  width="160" />
        </el-table>
        <div v-else class="empty-state">
          <div class="empty-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          </div>
          <p class="empty-text">暂无提供方</p>
        </div>
      </el-card>

      <!-- 消费方 -->
      <el-card shadow="never" class="section-card">
        <template #header>
          <div class="card-header-row">
            <span class="section-label">消费方</span>
            <span class="count-badge">{{ detail.consumers.length }}</span>
          </div>
        </template>
        <el-table v-if="detail.consumers.length" :data="detail.consumers" stripe>
          <el-table-column prop="server_ip"     label="服务器 IP" width="160" />
          <el-table-column prop="project_name"  label="项目名"    min-width="160" />
          <el-table-column prop="contact"       label="负责人"    width="120">
            <template #default="{ row }">{{ row.contact || '-' }}</template>
          </el-table-column>
          <el-table-column prop="registered_at" label="登记时间"  width="160" />
        </el-table>
        <div v-else class="empty-state">
          <div class="empty-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          </div>
          <p class="empty-text">暂无消费方登记</p>
        </div>
      </el-card>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { api } from '../api/index.js'

const route   = useRoute()
const loading = ref(false)
const detail  = ref(null)

async function load() {
  loading.value = true
  try {
    detail.value = await api.getServiceDetail(route.params.name)
  } finally {
    loading.value = false
  }
}

onMounted(load)
</script>

<style scoped>
/* 页面头部 */
.page-header {
  margin-bottom: 24px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.back-btn {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  background: none;
  border: none;
  cursor: pointer;
  color: var(--gray-medium);
  font-size: 13px;
  padding: 0;
  transition: color 0.15s;
}
.back-btn:hover { color: var(--orange); }

.header-main { display: flex; flex-direction: column; gap: 8px; }
.page-title {
  font-size: 22px;
  font-weight: 700;
  color: #111;
  letter-spacing: -0.02em;
}
.header-meta {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}
.service-id {
  font-family: 'Menlo', 'Consolas', monospace;
  font-size: 12px;
  background: rgba(0,0,0,0.06);
  padding: 2px 8px;
  border-radius: 6px;
  color: #555;
}

/* 状态标签 */
.status-badge {
  display: inline-flex;
  align-items: center;
  padding: 3px 10px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 500;
}
.status-online  { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
.status-offline { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }

/* 卡片区块 */
.section-card { margin-bottom: 16px; }
.card-header-row {
  display: flex;
  align-items: center;
  gap: 10px;
}
.section-label {
  font-size: 14px;
  font-weight: 600;
  color: #111;
}
.count-badge {
  display: inline-flex;
  align-items: center;
  padding: 2px 8px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 500;
  background: rgba(255,107,53,0.08);
  color: var(--orange);
}

/* 信息网格 */
.info-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0;
}
.info-item {
  padding: 12px 16px;
  border-bottom: 1px solid rgba(0,0,0,0.04);
  border-right: 1px solid rgba(0,0,0,0.04);
}
.info-item:nth-child(even) { border-right: none; }
.info-item--full {
  grid-column: span 2;
  border-right: none;
}
.info-key {
  font-size: 11px;
  font-weight: 600;
  color: #888;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  margin-bottom: 4px;
}
.info-value { font-size: 13.5px; color: var(--gray-dark); }
.info-link {
  font-size: 13.5px;
  color: var(--orange);
  text-decoration: none;
  word-break: break-all;
}
.info-link:hover { text-decoration: underline; }
.info-empty { font-size: 13.5px; color: #ccc; }

/* HTTP 方法标签 */
.method-badge {
  display: inline-flex;
  align-items: center;
  padding: 3px 8px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 700;
  font-family: 'Menlo', 'Consolas', monospace;
  letter-spacing: 0.03em;
}
.method-get    { background: #f0fdf4; color: #16a34a; }
.method-post   { background: rgba(255,107,53,0.08); color: var(--orange); }
.method-put    { background: #fffbeb; color: #d97706; }
.method-delete { background: #fef2f2; color: #dc2626; }

.path-code {
  font-family: 'Menlo', 'Consolas', monospace;
  font-size: 13px;
  color: #333;
}

/* 请求参数 */
.param-row {
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: 12px;
  line-height: 1.9;
  flex-wrap: wrap;
}
.param-name {
  font-family: 'Menlo', 'Consolas', monospace;
  font-size: 12px;
  background: rgba(0,0,0,0.05);
  padding: 1px 5px;
  border-radius: 4px;
}
.param-required {
  font-size: 10px;
  font-weight: 600;
  padding: 1px 5px;
  border-radius: 999px;
  background: #fef2f2;
  color: #dc2626;
}
.param-type { color: var(--gray-medium); font-size: 11px; }
.param-desc { color: #666; font-size: 12px; }

.response-pre {
  margin: 0;
  font-size: 12px;
  line-height: 1.5;
  white-space: pre-wrap;
  word-break: break-all;
  font-family: 'Menlo', 'Consolas', monospace;
  color: #444;
}

/* 空状态 */
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px 16px;
  gap: 8px;
}
.empty-icon {
  width: 40px;
  height: 40px;
  border-radius: 999px;
  background: var(--gray-light);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--gray-medium);
}
.empty-text {
  font-size: 13px;
  color: var(--gray-medium);
}
</style>
