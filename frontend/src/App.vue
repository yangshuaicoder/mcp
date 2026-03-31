<template>
  <el-config-provider :locale="zhCn">
    <div class="layout">
      <!-- 侧边栏 -->
      <aside class="sidebar">
        <div class="brand">
          <div class="brand-mark">M</div>
          <div class="brand-info">
            <span class="brand-name">MCP</span>
            <span class="brand-sub">管理后台</span>
          </div>
        </div>

        <nav class="nav">
          <router-link
            v-for="item in navItems"
            :key="item.to"
            :to="item.to"
            class="nav-item"
            :class="{ 'is-active': isActive(item.to) }"
          >
            <el-icon class="nav-icon"><component :is="item.icon" /></el-icon>
            <span>{{ item.label }}</span>
          </router-link>
        </nav>

        <div class="sidebar-footer">
          <span class="footer-label">微服务注册中心</span>
        </div>
      </aside>

      <!-- 主内容区 -->
      <main class="main">
        <router-view />
      </main>
    </div>
  </el-config-provider>
</template>

<script setup>
import zhCn from 'element-plus/dist/locale/zh-cn.mjs'
import { Grid, List, Connection, Tools } from '@element-plus/icons-vue'
import { useRoute } from 'vue-router'

const route = useRoute()

const navItems = [
  { to: '/',          label: '服务总览', icon: Grid       },
  { to: '/providers', label: '提供方',   icon: List       },
  { to: '/consumers', label: '消费方',   icon: Connection },
  { to: '/test',      label: '接口测试', icon: Tools      },
]

function isActive(path) {
  if (path === '/') return route.path === '/' || route.path.startsWith('/services')
  return route.path.startsWith(path)
}
</script>

<style>
/* ============================================================
   Design System Tokens
   ============================================================ */
:root {
  --orange:        #FF6B35;
  --orange-light:  #FF8F6B;
  --orange-dark:   #E85A2A;
  --beige:         #F5F5F0;
  --white:         #FFFFFF;
  --gray-dark:     #333333;
  --gray-medium:   #999999;
  --gray-light:    #F8F8F8;
  --gray-border:   #E5E5E5;
  --success:       #16a34a;
  --danger:        #dc2626;
  --warning:       #d97706;

  /* Element Plus primary = 品牌橙 */
  --el-color-primary:         #FF6B35;
  --el-color-primary-light-3: #FF8F6B;
  --el-color-primary-light-5: #FFAB8A;
  --el-color-primary-light-7: #FFC7A8;
  --el-color-primary-light-8: #FFD4BE;
  --el-color-primary-light-9: #FFF0EA;
  --el-color-primary-dark-2:  #E85A2A;
  --el-color-success:         #16a34a;
  --el-color-success-light-9: #f0fdf4;
  --el-color-danger:          #dc2626;
  --el-color-danger-light-9:  #fef2f2;
  --el-color-warning:         #d97706;
  --el-border-radius-base:    8px;
}

/* ============================================================
   Reset & Base
   ============================================================ */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
  background: var(--beige);
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Inter', system-ui, sans-serif;
  color: var(--gray-dark);
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

/* ============================================================
   Layout
   ============================================================ */
.layout {
  display: flex;
  min-height: 100vh;
}

/* ============================================================
   Sidebar
   ============================================================ */
.sidebar {
  width: 220px;
  min-width: 220px;
  background: #030712;
  display: flex;
  flex-direction: column;
  position: sticky;
  top: 0;
  height: 100vh;
  overflow: hidden;
  flex-shrink: 0;
}

.brand {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 20px 16px 18px;
  border-bottom: 1px solid rgba(255,255,255,0.05);
}

.brand-mark {
  width: 34px;
  height: 34px;
  border-radius: 9px;
  background: var(--orange);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 15px;
  font-weight: 800;
  flex-shrink: 0;
  letter-spacing: -0.02em;
}

.brand-info {
  display: flex;
  flex-direction: column;
  gap: 1px;
}

.brand-name {
  color: white;
  font-size: 15px;
  font-weight: 700;
  line-height: 1.2;
  letter-spacing: -0.02em;
}

.brand-sub {
  color: rgba(255,255,255,0.35);
  font-size: 11px;
  line-height: 1.2;
}

.nav {
  padding: 12px 10px;
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 9px;
  padding: 9px 12px;
  border-radius: 10px;
  text-decoration: none;
  color: #6b7280;
  font-size: 13.5px;
  font-weight: 400;
  transition: background 0.15s, color 0.15s;
}

.nav-item:hover {
  color: white;
  background: rgba(255,255,255,0.05);
}

.nav-item.is-active {
  background: rgba(255,107,53,0.12);
  color: var(--orange);
  font-weight: 600;
}

.nav-item.is-active:hover {
  background: rgba(255,107,53,0.15);
}

.nav-icon {
  font-size: 15px !important;
  flex-shrink: 0;
}

.sidebar-footer {
  padding: 14px 16px;
  border-top: 1px solid rgba(255,255,255,0.04);
}

.footer-label {
  font-size: 10px;
  color: rgba(255,255,255,0.18);
  font-family: 'Menlo', 'Consolas', monospace;
  letter-spacing: 0.06em;
  text-transform: uppercase;
}

/* ============================================================
   Main Content
   ============================================================ */
.main {
  flex: 1;
  padding: 28px 32px;
  min-width: 0;
}

/* ============================================================
   Element Plus Global Overrides
   ============================================================ */

/* Cards */
.el-card {
  border-radius: 16px !important;
  border: 1px solid rgba(0,0,0,0.05) !important;
  box-shadow: 0 1px 3px rgba(0,0,0,0.04) !important;
  transition: box-shadow 0.2s, transform 0.2s !important;
}
.el-card__header {
  padding: 16px 20px 14px !important;
  border-bottom: 1px solid rgba(0,0,0,0.05) !important;
  font-weight: 600;
  font-size: 14px;
  color: #111;
}
.el-card__body {
  padding: 20px !important;
}

/* Table */
.el-table {
  --el-table-header-bg-color: #f9f9f7;
  --el-table-row-hover-bg-color: rgba(255,107,53,0.03);
  font-size: 13.5px;
}
.el-table th.el-table__cell {
  font-size: 11px !important;
  font-weight: 600 !important;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #888 !important;
  background: #f9f9f7 !important;
}
.el-table td.el-table__cell {
  color: var(--gray-dark);
  font-size: 13.5px;
}

/* Buttons */
.el-button--primary {
  --el-button-bg-color: var(--orange);
  --el-button-border-color: var(--orange);
  --el-button-hover-bg-color: var(--orange-dark);
  --el-button-hover-border-color: var(--orange-dark);
  --el-button-active-bg-color: var(--orange-dark);
}
.el-button {
  border-radius: 8px !important;
  font-size: 13px !important;
}

/* Tags */
.el-tag {
  border-radius: 999px !important;
  font-size: 11px !important;
  font-weight: 500 !important;
}
.el-tag--success {
  background: #f0fdf4 !important;
  color: #16a34a !important;
  border-color: #bbf7d0 !important;
}
.el-tag--danger {
  background: #fef2f2 !important;
  color: #dc2626 !important;
  border-color: #fecaca !important;
}
.el-tag--info {
  background: rgba(255,107,53,0.06) !important;
  color: var(--orange) !important;
  border-color: rgba(255,107,53,0.2) !important;
}
.el-tag--warning {
  background: #fffbeb !important;
  color: #d97706 !important;
  border-color: #fde68a !important;
}

/* Inputs */
.el-input__wrapper {
  border-radius: 10px !important;
}
.el-select .el-input__wrapper {
  border-radius: 10px !important;
}
.el-input__wrapper.is-focus {
  box-shadow: 0 0 0 1px var(--orange) inset !important;
}

/* Pagination */
.el-pagination.is-background .el-pager li.is-active {
  background: var(--orange) !important;
}
.el-pagination.is-background .el-pager li:hover {
  color: var(--orange) !important;
}

/* Tabs */
.el-tabs__item.is-active { color: var(--orange) !important; }
.el-tabs__active-bar { background: var(--orange) !important; }
.el-tabs__item:hover { color: var(--orange-light) !important; }

/* Radio */
.el-radio__input.is-checked .el-radio__inner {
  border-color: var(--orange) !important;
  background: var(--orange) !important;
}
.el-radio__input.is-checked + .el-radio__label { color: var(--orange) !important; }

/* Descriptions */
.el-descriptions__label {
  font-size: 12px !important;
  color: #888 !important;
  font-weight: 500 !important;
  background: #fafaf8 !important;
}
.el-descriptions__content {
  font-size: 13.5px !important;
}

/* Page Header (el-page-header) */
.el-page-header__back .el-icon { color: var(--orange) !important; }
.el-page-header__back:hover { color: var(--orange) !important; }

/* Alert */
.el-alert--info { background: rgba(255,107,53,0.05) !important; border-color: rgba(255,107,53,0.15) !important; }
.el-alert--info .el-alert__title { color: var(--orange) !important; }
.el-alert--info .el-alert__icon { color: var(--orange) !important; }
</style>
