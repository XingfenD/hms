<template>
    <a-layout>
      <a-layout-header class="header">
        <div class="logo">
          <MedicineBoxOutlined style="color: #fff; font-size: 24px; margin-right: 12px;" />
          <span style="color: #fff; font-size: 18px; font-weight: 500;">智慧医疗管理平台 - 药房端</span>
        </div>
        <div class="user-info">
          <a-dropdown>
            <a class="ant-dropdown-link" @click.prevent>
              <UserOutlined style="color: #ffffff; text-shadow: 0 0 3px rgba(0, 0, 0, 0.5);" />
              <span style="margin-left: 8px; color: #ffffff; text-shadow: 0 0 3px rgba(0, 0, 0, 0.5);">{{ userInfo.name }}
                {{ userInfo.title }}</span>
              <DownOutlined style="margin-left: 8px; color: #ffffff; text-shadow: 0 0 3px rgba(0, 0, 0, 0.5);" />
            </a>
            <template #overlay>
              <a-menu>
                <a-menu-item key="0" @click="navigateTo('/doctor/profile')">
                  <SettingOutlined />
                  <span>个人设置</span>
                </a-menu-item>
                <a-menu-divider />
                <a-menu-item key="3" @click="handleLogout">
                  <LogoutOutlined />
                  <span>注销登录</span>
                </a-menu-item>
              </a-menu>
            </template>
          </a-dropdown>
        </div>
      </a-layout-header>
      <a-layout>
        <a-layout-sider width="200" style="background: #fff">
          <a-menu v-model:selectedKeys="selectedKeys" v-model:openKeys="openKeys" mode="inline"
            :style="{ height: '100%', borderRight: 0 }">
            <a-sub-menu key="sub1">
              <template #title>
                <span>
                  <UserOutlined />
                  医生功能
                </span>
              </template>
              <a-menu-item key="call" @click="navigateTo('/doctor/call')">呼叫患者</a-menu-item>
              <a-menu-item key="prescribe" @click="navigateTo('/doctor/prescribe')">开具处方</a-menu-item>
              <a-menu-item key="records" @click="navigateTo('/doctor/records')">就诊记录</a-menu-item>
              <!-- <a-menu-item key="prescriptions" @click="navigateTo('/doctor/prescriptions')">处方管理</a-menu-item> -->
            </a-sub-menu>
          </a-menu>
        </a-layout-sider>
        <a-layout style="flex: 1; padding: 0 24px 24px">
          <a-breadcrumb style="margin: 16px 0">
            <a-breadcrumb-item>首页</a-breadcrumb-item>
            <a-breadcrumb-item>{{ currentMenuTitle }}</a-breadcrumb-item>
          </a-breadcrumb>
          <a-layout-content
            :style="{ background: '#fff', padding: '24px', margin: 0, minHeight: 'calc(100vh - 180px)', borderRadius: '4px' }">
            <!-- 今日统计卡片 -->
            <div class="stats-cards" v-if="selectedKeys[0] === 'call'">
              <a-row :gutter="16">
                <a-col :span="8">
                  <a-card title="今日接诊" :bordered="false">
                    <div class="stat-card">
                      <span class="stat-number">{{ stats.todayPatients }}</span>
                      <span class="stat-unit">人</span>
                    </div>
                  </a-card>
                </a-col>
                <a-col :span="8">
                  <a-card title="当前排队" :bordered="false">
                    <div class="stat-card">
                      <span class="stat-number">{{ stats.waitingPatients }}</span>
                      <span class="stat-unit">人</span>
                    </div>
                  </a-card>
                </a-col>
                <a-col :span="8">
                  <a-card title="平均等待" :bordered="false">
                    <div class="stat-card">
                      <span class="stat-number">{{ stats.avgWaitTime }}</span>
                      <span class="stat-unit">分钟</span>
                    </div>
                  </a-card>
                </a-col>
              </a-row>
            </div>
  
            <!-- 动态内容区域 -->
            <router-view v-slot="{ Component }">
              <transition name="fade" mode="out-in">
                <component :is="Component" />
              </transition>
            </router-view>
          </a-layout-content>
        </a-layout>
      </a-layout>
      <a-layout-footer style="text-align: center; padding: 16px">
        智慧医疗管理平台 ©2025 版权所有
      </a-layout-footer>
    </a-layout>
  </template>
  
  <script setup>
  import { ref, computed, onMounted } from 'vue'
  import { useRouter, useRoute } from 'vue-router'
  import { message } from 'ant-design-vue'
  import {
    MedicineBoxOutlined,
    UserOutlined,
    DownOutlined,
    SettingOutlined,
    LogoutOutlined
  } from '@ant-design/icons-vue'
  import { loginout } from '@/api/api'
  
  const router = useRouter()
  const route = useRoute()
  
  // 用户信息
  const userInfo = ref({
    name: '张医生',
    title: '主任医师',
    department: '内科',
    role: 'doctor'
  })
  
  // 今日统计
  const stats = ref({
    todayPatients: 12,
    waitingPatients: 5,
    avgWaitTime: 15
  })
  
  // 菜单状态
  const selectedKeys = ref([route.matched[1]?.meta?.menuKey || 'call'])
  const openKeys = ref(['sub1'])
  
  // 当前菜单标题
  const menuTitles = {
    call: '呼叫患者',
    prescribe: '开具处方',
    records: '就诊记录',
    prescriptions: '处方管理'
  }
  const currentMenuTitle = computed(() => menuTitles[selectedKeys.value[0]] || '首页')
  
  // 导航方法
  const navigateTo = (path) => {
    router.push(path)
  }
  
  // 注销方法
  const handleLogout = async () => {
    try {
      const response = await loginout();
      console.log(response);
      if (response.code === 200) {
        message.success('注销成功，即将返回登录页面')
        router.push('/login');
      } else {
        message.error(response.message);
      }
    } catch (error) {
      message.error('注销请求出错，请稍后重试');
    }
  }
  
  onMounted(() => {
    navigateTo('/doctor/call')
  })
  </script>
  
  <style scoped>
  .header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 24px;
    background: linear-gradient(135deg, #1890ff 0%, #096dd9 100%);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    height: 64px;
  }
  
  .logo {
    display: flex;
    align-items: center;
    height: 64px;
  }
  
  .user-info {
    color: #fff;
    cursor: pointer;
  }
  
  .stats-cards {
    margin-bottom: 24px;
  }
  
  .stat-card {
    display: flex;
    align-items: baseline;
  }
  
  .stat-number {
    font-size: 32px;
    font-weight: 600;
    color: #1890ff;
    margin-right: 8px;
  }
  
  .stat-unit {
    font-size: 14px;
    color: rgba(0, 0, 0, 0.45);
  }
  
  /* 过渡动画 */
  .fade-enter-active,
  .fade-leave-active {
    transition: opacity 0.3s ease;
  }
  
  .fade-enter-from,
  .fade-leave-to {
    opacity: 0;
  }
  
  /* 内容区域样式 */
  .ant-layout-content {
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
  }
  </style>