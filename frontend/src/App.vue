<template>
  <el-config-provider :locale="zhCn">
    <el-container class="app-container">
      <el-header class="app-header">
        <div class="logo">
          <el-icon size="20"><Grid /></el-icon>
          <span>MCP 管理后台</span>
        </div>
        <el-menu
          mode="horizontal"
          :router="true"
          :default-active="$route.path"
          background-color="#001529"
          text-color="#ffffffa0"
          active-text-color="#ffffff"
          class="nav-menu"
        >
          <el-menu-item index="/">服务总览</el-menu-item>
          <el-menu-item index="/providers">提供方列表</el-menu-item>
          <el-menu-item index="/consumers">消费方列表</el-menu-item>
          <el-menu-item index="/test">接口测试</el-menu-item>
        </el-menu>
      </el-header>
      <el-main class="app-main">
        <router-view />
      </el-main>
    </el-container>
  </el-config-provider>
</template>

<script setup>
import zhCn from 'element-plus/dist/locale/zh-cn.mjs'
import { Grid } from '@element-plus/icons-vue'
</script>

<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { background: #f0f2f5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }

.app-container { min-height: 100vh; }

.app-header {
  display: flex;
  align-items: center;
  background: #001529;
  padding: 0 24px;
  height: 56px !important;
  gap: 32px;
}

.logo {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #fff;
  font-size: 16px;
  font-weight: 600;
  white-space: nowrap;
}

.nav-menu {
  border-bottom: none !important;
  flex: 1;
}
.nav-menu .el-menu-item { height: 56px; line-height: 56px; }

.app-main { padding: 24px; }
</style>
