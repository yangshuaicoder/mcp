<template>
  <div>
    <el-card shadow="never" style="margin-bottom: 16px;">
      <template #header>
        <div style="display:flex; align-items:center; justify-content:space-between;">
          <b>提供方列表</b>
          <el-button :icon="Refresh" @click="load">刷新</el-button>
        </div>
      </template>
      <el-input
        v-model="keyword"
        placeholder="搜索服务器 IP / 项目名"
        clearable
        :prefix-icon="Search"
        style="max-width:300px; margin-bottom:16px;"
        @input="onSearch"
      />
      <el-table :data="filtered" v-loading="loading" stripe>
        <el-table-column prop="server_ip"    label="服务器 IP"  width="160" />
        <el-table-column prop="project_name" label="项目名"     min-width="160" />
        <el-table-column label="提供的服务" min-width="260">
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
import axios from 'axios'

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

let searchTimer = null
function onSearch() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {}, 300)
}

async function load() {
  loading.value = true
  try {
    const res = await axios.get('/api/v1/services', { params: { status: 'all', page_size: 100 } })
    const services = res.data.data?.list || []

    const map = {}
    for (const svc of services) {
      const provRes = await axios.get(`/api/v1/services/${svc.name}`)
      const providers = provRes.data.data?.providers || []
      for (const p of providers) {
        const key = `${p.server_ip}|${p.project_name}`
        if (!map[key]) {
          map[key] = { server_ip: p.server_ip, project_name: p.project_name, contact: p.contact, services: [] }
        }
        map[key].services.push({ name: svc.name, display_name: svc.display_name, status: svc.status })
      }
    }
    rows.value = Object.values(map)
  } finally {
    loading.value = false
  }
}

onMounted(load)
</script>
