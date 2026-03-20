<template>
  <div>
    <!-- 统计卡片 -->
    <el-row :gutter="16" class="stats-row">
      <el-col :span="6">
        <el-card shadow="never" class="stat-card stat-total">
          <div class="stat-number">{{ stats.total }}</div>
          <div class="stat-label">全部服务</div>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card shadow="never" class="stat-card stat-online">
          <div class="stat-number">{{ stats.online }}</div>
          <div class="stat-label">在线服务</div>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card shadow="never" class="stat-card stat-offline">
          <div class="stat-number">{{ stats.offline }}</div>
          <div class="stat-label">离线服务</div>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card shadow="never" class="stat-card stat-category">
          <div class="stat-number">{{ stats.categories }}</div>
          <div class="stat-label">服务分类</div>
        </el-card>
      </el-col>
    </el-row>

    <!-- 筛选工具栏 -->
    <el-card shadow="never" style="margin-bottom: 16px;">
      <el-row :gutter="12" align="middle">
        <el-col :span="8">
          <el-input
            v-model="filter.keyword"
            placeholder="搜索服务名称/描述"
            clearable
            :prefix-icon="Search"
            @input="onSearch"
          />
        </el-col>
        <el-col :span="5">
          <el-select v-model="filter.category" placeholder="全部分类" clearable @change="loadServices">
            <el-option v-for="c in categories" :key="c" :label="c" :value="c" />
          </el-select>
        </el-col>
        <el-col :span="5">
          <el-select v-model="filter.status" @change="loadServices">
            <el-option label="在线" value="online" />
            <el-option label="离线" value="offline" />
            <el-option label="全部" value="all" />
          </el-select>
        </el-col>
        <el-col :span="6" style="text-align:right;">
          <el-button :icon="Refresh" @click="loadServices">刷新</el-button>
        </el-col>
      </el-row>
    </el-card>

    <!-- 服务列表 -->
    <el-card shadow="never">
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
            <el-badge :type="row.status === 'online' ? 'success' : 'danger'" is-dot style="margin-right:6px" />
            <span>{{ row.status === 'online' ? '在线' : '离线' }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="base_url" label="服务地址" min-width="200" show-overflow-tooltip />
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
          @size-change="loadServices"
          @current-change="loadServices"
        />
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { Search, Refresh } from '@element-plus/icons-vue'
import { api } from '../api/index.js'

const loading = ref(false)
const services = ref([])
const allServicesForStats = ref([])

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
  } finally {
    loading.value = false
  }
}

async function loadStats() {
  const [all, online, offline] = await Promise.all([
    api.getServices({ status: 'all', page_size: 100 }),
    api.getServices({ status: 'online', page_size: 1 }),
    api.getServices({ status: 'offline', page_size: 1 }),
  ])
  stats.total = all.total
  stats.online = online.total
  stats.offline = offline.total

  const cats = new Set(all.list.map(s => s.category).filter(Boolean))
  stats.categories = cats.size
  categories.value = [...cats]
}

onMounted(() => {
  loadServices()
  loadStats()
})
</script>

<style scoped>
.stats-row { margin-bottom: 16px; }

.stat-card { text-align: center; padding: 8px 0; }
.stat-number { font-size: 32px; font-weight: 700; line-height: 1.2; }
.stat-label { font-size: 13px; color: #666; margin-top: 4px; }

.stat-total .stat-number  { color: #409eff; }
.stat-online .stat-number { color: #67c23a; }
.stat-offline .stat-number{ color: #f56c6c; }
.stat-category .stat-number{ color: #e6a23c; }

.service-link { color: #409eff; text-decoration: none; font-weight: 500; }
.service-link:hover { text-decoration: underline; }

.pagination-wrap { display: flex; justify-content: flex-end; margin-top: 16px; }
</style>
