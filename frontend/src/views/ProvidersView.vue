<template>
  <div>
    <!-- 页面标题 -->
    <div class="page-header">
      <div>
        <h1 class="page-title">提供方列表</h1>
        <p class="page-desc">各节点提供的微服务汇总视图</p>
      </div>
    </div>

    <el-card shadow="never">
      <div class="toolbar">
        <el-input
          v-model="keyword"
          placeholder="搜索服务器 IP / 项目名"
          clearable
          :prefix-icon="Search"
          class="toolbar-search"
        />
        <el-button :icon="Refresh" @click="load" class="ml-auto">刷新</el-button>
      </div>

      <el-table :data="filtered" v-loading="loading" stripe>
        <el-table-column prop="internal_ip"  label="内网 IP"  width="160" />
        <el-table-column prop="external_ip"  label="外网 IP"  width="160" />
        <el-table-column prop="project_name" label="项目名"   min-width="160" />
        <el-table-column label="提供的服务" min-width="260">
          <template #default="{ row }">
            <div class="tag-group">
              <router-link
                v-for="s in row.services"
                :key="s.name"
                :to="`/services/${s.name}`"
                class="svc-tag"
                :class="s.status === 'online' ? 'svc-tag--online' : 'svc-tag--offline'"
              >
                {{ s.display_name || s.name }}
              </router-link>
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="contact" label="负责人" width="120">
          <template #default="{ row }">
            <span class="contact">{{ row.contact || '-' }}</span>
          </template>
        </el-table-column>
      </el-table>

      <div v-if="!loading && filtered.length === 0" class="empty-state">
        <div class="empty-icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <p class="empty-text">暂无提供方数据</p>
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Search, Refresh } from '@element-plus/icons-vue'
import { api, fetchAllServices } from '../api/index.js'

const loading = ref(false)
const rows    = ref([])
const keyword = ref('')

const filtered = computed(() => {
  if (!keyword.value) return rows.value
  const k = keyword.value.toLowerCase()
  return rows.value.filter(r =>
    r.internal_ip.toLowerCase().includes(k) ||
    r.external_ip.toLowerCase().includes(k) ||
    r.project_name.toLowerCase().includes(k)
  )
})

async function load() {
  loading.value = true
  try {
    const data = await fetchAllServices({ status: 'all' })
    const services = data.list || []

    const details = await Promise.all(services.map(svc => api.getServiceDetail(svc.name)))

    const map = {}
    services.forEach((svc, i) => {
      const providers = details[i]?.providers || []
      for (const p of providers) {
        const key = `${p.internal_ip}|${p.project_name}`
        if (!map[key]) {
          map[key] = { internal_ip: p.internal_ip, external_ip: p.external_ip, project_name: p.project_name, contact: p.contact, services: [] }
        }
        map[key].services.push({ name: svc.name, display_name: svc.display_name, status: svc.status })
      }
    })
    rows.value = Object.values(map)
  } catch (e) {
    // 错误由 api 拦截器统一处理
  } finally {
    loading.value = false
  }
}

onMounted(load)
</script>

<style scoped>
.page-header { margin-bottom: 24px; }
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

.toolbar {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 16px;
}
.toolbar-search { max-width: 280px; }
.ml-auto { margin-left: auto; }

/* 服务标签 */
.tag-group {
  display: flex;
  flex-wrap: wrap;
  gap: 5px;
}
.svc-tag {
  display: inline-flex;
  align-items: center;
  padding: 3px 10px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 500;
  text-decoration: none;
  transition: opacity 0.15s;
}
.svc-tag:hover { opacity: 0.75; }
.svc-tag--online  { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
.svc-tag--offline { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }

.contact {
  font-size: 13px;
  color: var(--gray-medium);
}

/* 空状态 */
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 48px 16px;
  gap: 8px;
}
.empty-icon {
  width: 44px;
  height: 44px;
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
