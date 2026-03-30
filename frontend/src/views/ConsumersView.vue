<template>
  <div>
    <el-card shadow="never" style="margin-bottom: 16px;">
      <template #header>
        <div style="display:flex; align-items:center; justify-content:space-between;">
          <b>消费方列表</b>
          <el-button :icon="Refresh" @click="load">刷新</el-button>
        </div>
      </template>
      <el-input
        v-model="keyword"
        placeholder="搜索服务器 IP / 项目名"
        clearable
        :prefix-icon="Search"
        style="max-width:300px; margin-bottom:16px;"
      />
      <el-table :data="filtered" v-loading="loading" stripe>
        <el-table-column prop="server_ip"    label="服务器 IP"  width="160" />
        <el-table-column prop="project_name" label="项目名"     min-width="160" />
        <el-table-column label="使用的服务" min-width="260">
          <template #default="{ row }">
            <el-tag
              v-for="s in row.services"
              :key="s.name"
              :type="s.status === 'online' ? 'success' : 'danger'"
              size="small"
              style="margin: 2px;"
            >
              <router-link :to="`/services/${s.name}`" style="color:inherit; text-decoration:none;">
                {{ s.display_name || s.name }}
              </router-link>
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="contact" label="负责人" width="120">
          <template #default="{ row }">{{ row.contact || '-' }}</template>
        </el-table-column>
      </el-table>
    </el-card>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Search, Refresh } from '@element-plus/icons-vue'
import { api, fetchAllServices } from '../api/index.js'

const loading = ref(false)
const rows = ref([])
const keyword = ref('')

const filtered = computed(() => {
  if (!keyword.value) return rows.value
  const k = keyword.value.toLowerCase()
  return rows.value.filter(r =>
    r.server_ip.toLowerCase().includes(k) ||
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
      const consumers = details[i]?.consumers || []
      for (const c of consumers) {
        const key = `${c.server_ip}|${c.project_name}`
        if (!map[key]) {
          map[key] = { server_ip: c.server_ip, project_name: c.project_name, contact: c.contact, services: [] }
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
