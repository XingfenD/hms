<template>
  <a-layout>
    <a-layout-header class="header">
      <div class="logo">
        <MedicineBoxOutlined style="color: #fff; font-size: 24px; margin-right: 12px;" />
        <span style="color: #fff; font-size: 18px; font-weight: 500;">智慧医疗管理平台 - 用户端</span>
      </div>
      <div class="user-info">
        <a-dropdown>
          <a class="ant-dropdown-link" @click.prevent>
            <UserOutlined style="color: #ffffff; text-shadow: 0 0 3px rgba(0, 0, 0, 0.5);"/>
            <span style="margin-left: 8px; color: #ffffff; text-shadow: 0 0 3px rgba(0, 0, 0, 0.5);">{{ userInfo.Username }}</span>
            <DownOutlined style="margin-left: 8px; color: #ffffff; text-shadow: 0 0 3px rgba(0, 0, 0, 0.5);" />
          </a>
          <template #overlay>
            <a-menu>
              <a-menu-item key="0" @click="navigateTo('/patient/profile')">
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
                患者功能
              </span>
            </template>
            <a-menu-item key="register" @click="navigateTo('/patient/register')">挂号</a-menu-item>
            <a-menu-item key="appointments" @click="navigateTo('/patient/appointments')">预约管理</a-menu-item>
            <a-menu-item key="payments" @click="navigateTo('/patient/payments')">缴费</a-menu-item>
            <a-menu-item key="records" @click="navigateTo('/patient/records')">就诊记录</a-menu-item>
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
          <!-- 签到状态 -->
          <div v-if="showSignIn" class="sign-in-container">
            <a-alert v-if="!isSigned" message="您尚未签到" description="请先签到以使用挂号功能" type="info" show-icon closable>
              <template #action>
                <a-button type="primary" size="small" @click="signIn">立即签到</a-button>
              </template>
            </a-alert>
            <a-alert v-else message="签到成功" :description="`您已于 ${signInTime} 完成签到`" type="success" show-icon closable />
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
    <ChatAssistant/>
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
import { getLoginInfo, get_single_userinfo_data } from '@/api/api' // 引入接口
import ChatAssistant from '@/components/ChatAssistant.vue'

const router = useRouter()
const route = useRoute()

// 用户信息
const userInfo = ref({})

// 菜单状态
const selectedKeys = ref([route.matched[1]?.meta?.menuKey || 'register'])
const openKeys = ref(['sub1'])
console.log(selectedKeys);

// 签到状态
const isSigned = ref(false)
const signInTime = ref('')
const showSignIn = computed(() => route.path.includes('/patient/register'))

// 当前菜单标题
const menuTitles = {
  register: '预约挂号',
  appointments: '预约管理',
  payments: '费用缴纳',
  records: '就诊记录'
}
const currentMenuTitle = computed(() => menuTitles[selectedKeys.value[0]] || '首页')

// 导航方法
const navigateTo = (path) => {
  router.push(path)
}

// 签到方法
const signIn = () => {
  if (!isSigned.value) {
    isSigned.value = true
    const now = new Date()
    signInTime.value = now.toLocaleString()
    message.success('签到成功！')
  }
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
    console.log(loginInfo.data);
    const userId = loginInfo.data.data.user_id
    const userData = await get_single_userinfo_data(userId)
    console.log(userData.data);
    userInfo.value = userData.data.data[0]

  } catch (error) {
    message.error('获取用户信息失败，请稍后重试')
  }
}

// 在组件挂载完成后，获取用户信息并导航到挂号页面
onMounted(async () => {
  await fetchUserInfo()
  navigateTo('/patient/register')
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

.sign-in-container {
  margin-bottom: 24px;
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