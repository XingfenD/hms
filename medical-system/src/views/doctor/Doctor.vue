<template>
  <a-layout>
    <a-layout-header class="header">
      <div class="logo">
        <MedicineBoxOutlined style="color: #fff; font-size: 24px; margin-right: 12px;" />
        <span style="color: #fff; font-size: 18px; font-weight: 500;">智慧医疗管理平台 - 医生端</span>
      </div>
      <div class="user-info">
        <a-dropdown>
          <a class="ant-dropdown-link" @click.prevent>
            <UserOutlined style="color: #ffffff; text-shadow: 0 0 3px rgba(0, 0, 0, 0.5);" />
            <span style="margin-left: 8px; color: #ffffff; text-shadow: 0 0 3px rgba(0, 0, 0, 0.5);">{{
              userInfo.Username }}</span>
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
            <a-menu-item key="checkup" @click="navigateTo('/doctor/checkup')">开具检查</a-menu-item>
            <a-menu-item key="checkupRecords" @click="navigateTo('/doctor/checkupRecords')">检查记录</a-menu-item>
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
          <!-- 替换为 Ant Design 日历 -->
          <div class="schedule-calendar" v-if="selectedKeys[0] === 'call'">
            <a-card title="我的排班日历" :bordered="false">
              <a-calendar>
                <template #dateCellRender="{ current }">
                  <ul class="events">
                    <li v-for="item in getScheduleData(current)" :key="item.id">
                      <a-badge :status="item.type" :text="item.content" />
                    </li>
                  </ul>
                </template>
                <template #monthCellRender="{ current }">
                  <div v-if="getMonthData(current)" class="notes-month">
                    <section>{{ getMonthData(current) }}</section>
                    <span>排班天数</span>
                  </div>
                </template>
              </a-calendar>
            </a-card>
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
    <ChatAssistant />
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
import { loginout, getLoginInfo, get_single_userinfo_data, getScheduleByDoctor } from '@/api/api'
import dayjs from 'dayjs'
import VueCalendarHeatmap from 'vue-calendar-heatmap'

const router = useRouter()
const route = useRoute()

// 用户信息
const userInfo = ref({})

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
  checkup: '开具检查',
  checkupRecords: '检查记录',
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

// 获取登录用户信息
const fetchUserInfo = async () => {
  try {
    const loginInfo = await getLoginInfo()
    const userId = loginInfo.data.data.user_id
    const userData = await get_single_userinfo_data(userId)
    console.log(loginInfo.data, userData.data);
    userInfo.value = userData.data.data[0]
  } catch (error) {
    message.error('获取用户信息失败，请稍后重试')
  }
}


// 排班数据
const scheduleData = ref([])

// 获取医生排班信息
const fetchSchedule = async () => {
  try {
    const loginInfo = await getLoginInfo()
    const doctorId = loginInfo.data.data.doctor_id
    const response = await getScheduleByDoctor(doctorId)
    scheduleData.value = response.data.data.map(item => ({
      ...item,
      date: dayjs(item.ScheduleDate).format('YYYY-MM-DD'),
      type: 'success', // 可以自定义不同类型
      content: `${item.StartTime}-${item.EndTime} 门诊`
    }))
    
    // 如果没有数据，添加一些示例数据
    if (scheduleData.value.length === 0) {
      scheduleData.value = [
        {
          id: 1,
          date: dayjs().format('YYYY-MM-DD'),
          type: 'success',
          content: '09:00-12:00 门诊'
        },
        {
          id: 2,
          date: dayjs().add(1, 'day').format('YYYY-MM-DD'),
          type: 'warning',
          content: '14:00-17:00 专家门诊'
        },
        {
          id: 3,
          date: dayjs().add(3, 'day').format('YYYY-MM-DD'),
          type: 'success',
          content: '08:30-11:30 门诊'
        }
      ]
    }
  } catch (error) {
    console.error('获取排班信息失败:', error)
    message.error('获取排班信息失败，请稍后重试')
  }
}

// 获取某天的排班数据
const getScheduleData = (current) => {
  const dateStr = current.format('YYYY-MM-DD')
  return scheduleData.value.filter(item => item.date === dateStr)
}

// 获取某月的排班天数
const getMonthData = (current) => {
  const monthStr = current.format('YYYY-MM')
  const count = scheduleData.value.filter(item => 
    item.date.startsWith(monthStr)
  ).length
  return count > 0 ? count : null
}

onMounted(async () => {
  await fetchUserInfo()
  await fetchSchedule()
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

/* 新增日历样式 */
.events {
  list-style: none;
  margin: 0;
  padding: 0;
}
.events .ant-badge-status {
  overflow: hidden;
  white-space: nowrap;
  width: 100%;
  text-overflow: ellipsis;
  font-size: 12px;
}
.notes-month {
  text-align: center;
  font-size: 28px;
}
.notes-month section {
  font-size: 28px;
}
</style>