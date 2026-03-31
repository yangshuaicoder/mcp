<template>
  <div>
    <!-- 页面标题 -->
    <div class="page-header">
      <div>
        <h1 class="page-title">服务总览</h1>
        <p class="page-desc">查看和管理所有已注册的微服务</p>
      </div>
    </div>

    <!-- 统计卡片 -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon stat-icon--blue">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
        </div>
        <div>
          <div class="stat-number">{{ stats.total }}</div>
          <div class="stat-label">全部服务</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon stat-icon--green">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div>
          <div class="stat-number stat-number--green">{{ stats.online }}</div>
          <div class="stat-label">在线服务</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon stat-icon--red">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        </div>
        <div>
          <div class="stat-number stat-number--red">{{ stats.offline }}</div>
          <div class="stat-label">离线服务</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon stat-icon--orange">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
        </div>
        <div>
          <div class="stat-number stat-number--orange">{{ stats.categories }}</div>
          <div class="stat-label">服务分类</div>
        </div>
      </div>
    </div>

    <!-- 筛选 + 表格 -->
    <el-card shadow="never">
      <div class="filter-bar">
        <el-input
          v-model="filter.keyword"
          placeholder="搜索服务名称 / 描述"
          clearable
          :prefix-icon="Search"
          class="filter-search"
          @input="onSearch"
        />
        <el-select v-model="filter.category" placeholder="全部分类" clearable @change="loadServices" class="filter-select">
          <el-option v-for="c in categories" :key="c" :label="c" :value="c" />
        </el-select>
        <el-select v-model="filter.status" @change="loadServices" class="filter-select">
          <el-option label="在线" value="online" />
          <el-option label="离线" value="offline" />
          <el-option label="全部" value="all" />
        </el-select>
        <el-button :icon="Refresh" @click="loadServices" class="ml-auto">刷新</el-button>
      </div>

      <el-table :data="services" v-loading="loading" stripe style="width:100%">
        <el-table-column prop="name" label="服务标识" min-width="160">
          <template #default="{ row }">
            <router-link :to="`/services/${row.name}`" class="service-link">
              {{ row.name }}
            </router-link>
          </template>
        </el-table-column>
        <el-table-column prop="display_name" label="展示名" min-width="140" />
        <el-table-column prop="category" label="分类" width="120">
          <template #default="{ row }">
            <el-tag size="small" type="info">{{ row.category }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="status" label="状态" width="90">
          <template #default="{ row }">
            <span class="status-badge" :class="row.status === 'online' ? 'status-online' : 'status-offline'">
              {{ row.status === 'online' ? '在线' : '离线' }}
            </span>
          </template>
        </el-table-column>
        <el-table-column prop="internal_url" label="内网地址" min-width="200" show-overflow-tooltip />
        <el-table-column prop="last_heartbeat_at" label="最后心跳" width="160" />
        <el-table-column label="操作" width="80" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" link @click="$router.push(`/services/${row.name}`)">详情</el-button>
          </template>
        </el-table-column>
      </el-table>

      <div class="pagination-wrap">
        <el-pagination
          v-model:current-page="pagination.page"
          v-model:page-size="pagination.pageSize"
          :total="pagination.total"
          :page-sizes="[20, 50, 100]"
          layout="total, sizes, prev, pager, next"
          background
          @size-change="loadServices"
          @current-change="loadServices"
        />
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { Search, Refresh } from '@element-plus/icons-vue'
import { api, fetchAllServices } from '../api/index.js'

const loading = ref(false)
const services = ref([])

const stats = reactive({ total: 0, online: 0, offline: 0, categories: 0 })
const categories = ref([])

const filter = reactive({ keyword: '', category: '', status: 'online' })
const pagination = reactive({ page: 1, pageSize: 20, total: 0 })

let searchTimer = null
function onSearch() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => { pagination.page = 1; loadServices() }, 400)
}

async function loadServices() {
  loading.value = true
  try {
    const params = {
      page: pagination.page,
      page_size: pagination.pageSize,
      status: filter.status || 'all',
    }
    if (filter.keyword) params.keyword = filter.keyword
    if (filter.category) params.category = filter.category

    const data = await api.getServices(params)
    services.value = data.list
    pagination.total = data.total
  } catch (e) {
    // 错误由 api 拦截器统一提示
  } finally {
    loading.value = false
  }
}

async function loadStats() {
  try {
    const [totals, allServices] = await Promise.all([
      Promise.all([
        api.getServices({ status: 'all',     page_size: 1 }),
        api.getServices({ status: 'online',  page_size: 1 }),
        api.getServices({ status: 'offline', page_size: 1 }),
      ]),
      fetchAllServices({ status: 'all' }),
    ])
    const [all, online, offline] = totals
    stats.total   = all.total
    stats.online  = online.total
    stats.offline = offline.total

    const cats = new Set(allServices.list.map(s => s.category).filter(Boolean))
    stats.categories = cats.size
    categories.value = [...cats]
  } catch (e) {
    // 错误由 api 拦截器统一提示
  }
}

onMounted(() => {
  loadServices()
  loadStats()
})
</script>

<style scoped>
/* 页面标题 */
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

/* 统计卡片 */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 14px;
  margin-bottom: 20px;
}
.stat-card {
  background: white;
  border-radius: 16px;
  border: 1px solid rgba(0,0,0,0.05);
  padding: 18px 20px;
  display: flex;
  align-items: center;
  gap: 14px;
  transition: box-shadow 0.2s, transform 0.2s;
}
.stat-card:hover {
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  transform: translateY(-2px);
}
.stat-icon {
  width: 42px;
  height: 42px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.stat-icon--blue   { background: rgba(59,130,246,0.1);  color: #3b82f6; }
.stat-icon--green  { background: rgba(22,163,74,0.1);   color: #16a34a; }
.stat-icon--red    { background: rgba(220,38,38,0.1);   color: #dc2626; }
.stat-icon--orange { background: rgba(255,107,53,0.1);  color: var(--orange); }

.stat-number {
  font-size: 26px;
  font-weight: 700;
  line-height: 1.1;
  color: #111;
  letter-spacing: -0.02em;
}
.stat-number--green  { color: #16a34a; }
.stat-number--red    { color: #dc2626; }
.stat-number--orange { color: var(--orange); }

.stat-label {
  font-size: 12px;
  color: var(--gray-medium);
  margin-top: 3px;
}

/* 筛选栏 */
.filter-bar {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 16px;
  flex-wrap: wrap;
}
.filter-search { flex: 1; min-width: 200px; max-width: 280px; }
.filter-select { width: 130px; }
.ml-auto { margin-left: auto; }

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

/* 服务链接 */
.service-link {
  color: var(--orange);
  text-decoration: none;
  font-weight: 500;
  font-size: 13.5px;
}
.service-link:hover { text-decoration: underline; }

/* 分页 */
.pagination-wrap {
  display: flex;
  justify-content: flex-end;
  margin-top: 16px;
  padding-top: 16px;
  border-top: 1px solid rgba(0,0,0,0.05);
}
</style>
