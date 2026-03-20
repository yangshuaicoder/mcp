import axios from 'axios'
import { ElMessage } from 'element-plus'

const http = axios.create({
  baseURL: '/api/v1',
  timeout: 10000,
  headers: { 'Content-Type': 'application/json' },
})

http.interceptors.response.use(
  res => {
    const data = res.data
    if (data.code !== 0) {
      ElMessage.error(data.message || '请求失败')
      return Promise.reject(new Error(data.message))
    }
    return data.data
  },
  err => {
    ElMessage.error(err.message || '网络错误')
    return Promise.reject(err)
  }
)

export const api = {
  // 服务列表
  getServices(params = {}) {
    return http.get('/services', { params })
  },
  // 服务详情
  getServiceDetail(name) {
    return http.get(`/services/${name}`)
  },
  // 服务统计（从列表取）
  getStats() {
    return http.get('/services', { params: { status: 'all', page_size: 1 } })
  },
  getOnlineCount() {
    return http.get('/services', { params: { status: 'online', page_size: 1 } })
  },
  getOfflineCount() {
    return http.get('/services', { params: { status: 'offline', page_size: 1 } })
  },
}
