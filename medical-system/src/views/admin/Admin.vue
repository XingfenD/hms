<template>
  <div class="admin-dashboard">
    <!-- 顶部导航栏 -->
    <a-layout>
      <a-layout-header class="header">
        <div class="logo">
          <medicine-box-outlined style="color: #fff; font-size: 24px; margin-right: 12px;" />
          <span style="color: #fff; font-size: 18px; font-weight: 500;">智慧医疗管理平台</span>
        </div>
        <div class="user-info">
          <a-dropdown>
            <a class="ant-dropdown-link" @click.prevent>
              <UserOutlined style="color: #ffffff; text-shadow: 0 0 3px rgba(0, 0, 0, 0.5);"/>
            <span style="margin-left: 8px; color: #ffffff; text-shadow: 0 0 3px rgba(0, 0, 0, 0.5);">管理员</span>
            <DownOutlined style="margin-left: 8px; color: #ffffff; text-shadow: 0 0 3px rgba(0, 0, 0, 0.5);" />
            </a>
            <template #overlay>
              <a-menu>
                <a-menu-item key="0">
                  <setting-outlined />
                  <span>个人设置</span>
                </a-menu-item>
                <a-menu-divider />
                <a-menu-item key="3" @click="handleLogout">
                  <logout-outlined />
                  <span>注销登录</span>
                </a-menu-item>
              </a-menu>
            </template>
          </a-dropdown>
        </div>
      </a-layout-header>

      <!-- 主内容区 -->
      <a-layout-content class="content">
        <div class="content-wrapper">
          <a-page-header title="管理员仪表盘" sub-title="系统管理中心" class="page-header" />

          <!-- 功能卡片区 -->
          <div class="dashboard-cards">
            <a-row :gutter="[24, 24]">
              <a-col :xs="24" :sm="12" :md="8" :lg="6" v-for="item in menuItems" :key="item.key">
                <a-card hoverable class="feature-card" @click="navigateTo(item.path)">
                  <template #cover>
                    <div class="card-icon" :style="{ backgroundColor: item.color }">
                      <component :is="item.icon" class="feature-icon" />
                    </div>
                  </template>
                  <a-card-meta :title="item.title" :description="item.desc">
                    <template #avatar>
                      <a-avatar :style="{ backgroundColor: item.color }">
                        <component :is="item.icon" class="avatar-icon" />
                      </a-avatar>
                    </template>
                  </a-card-meta>
                </a-card>
              </a-col>
            </a-row>
          </div>
        </div>
      </a-layout-content>

      <!-- 底部 -->
      <a-layout-footer class="footer">
        智慧医疗管理平台 ©2025 版权所有
      </a-layout-footer>
    </a-layout>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import {
  MedicineBoxOutlined,
  UserOutlined,
  DownOutlined,
  SettingOutlined,
  LogoutOutlined,
  TeamOutlined,
  ScheduleOutlined,
  SearchOutlined,
  FileSearchOutlined,
  DatabaseOutlined
} from '@ant-design/icons-vue';
import { message } from 'ant-design-vue';
import { loginout } from '@/api/api';

const router = useRouter();

const menuItems = ref([
  {
    key: 'user',
    title: '用户管理',
    desc: '管理系统所有用户账户',
    icon: TeamOutlined,
    path: '/admin/user-manage',
    color: '#1890ff'
  },
  {
    key: 'schedule',
    title: '医生排班',
    desc: '管理医生出诊时间表',
    icon: ScheduleOutlined,
    path: '/admin/doctor-schedule',
    color: '#13c2c2'
  },
  {
    key: 'registration',
    title: '患者挂号查询',
    desc: '查询和管理患者挂号信息',
    icon: SearchOutlined,
    path: '/admin/patient-registration',
    color: '#722ed1'
  },
  {
    key: 'drug',
    title: '药品管理',
    desc: '管理药品库存和药品信息',
    icon: MedicineBoxOutlined,
    path: '/admin/drug-manage',
    color: '#fa8c16'
  },
  {
    key: 'doctor',
    title: '检查项目管理',
    desc: '管理检查项目信息',
    icon: FileSearchOutlined,
    path: '/admin/doctor-manage',
    color: '#f5222d'
  },
  {
    key: 'database',
    title: '数据库管理',
    desc: '系统数据库维护',
    icon: DatabaseOutlined,
    path: '/admin/db-manage',
    color: '#52c41a'
  }
]);

const navigateTo = (path) => {
  router.push(path);
};

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
</script>

<style scoped>
.admin-dashboard {
  height: 100vh;
  display: flex;
  flex-direction: column;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 24px;
  background: linear-gradient(135deg, #096dd9 0%, #1890ff 100%);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.logo {
  display: flex;
  align-items: center;
}

.user-info {
  color: #fff;
}

.content-wrapper {
  max-width: 1400px;  /* 增大最大宽度 */
  margin: 0 auto;
  padding: 24px 36px; /* 增加左右内边距 */
}

.dashboard-cards {
  width: 100%;
}

/* 卡片主图标样式 */
.feature-icon {
  font-size: 48px !important;  /* 增大主图标 */
  transition: all 0.3s ease;
}

/* 头像内图标样式 */
.avatar-icon {
  font-size: 18px !important;  /* 适当增大头像图标 */
}

.card-icon {
  height: 180px; /* 适当增加高度容纳大图标 */
  display: flex;
  align-items: center;
  justify-content: center;
}

/* 调整卡片悬停效果 */
.feature-card:hover .feature-icon {
  transform: scale(1.1); /* 图标单独放大效果 */
}

.feature-card {
  border-radius: 12px; /* 增大圆角 */
  transition: all 0.3s cubic-bezier(0.18, 0.89, 0.32, 1.28); /* 更弹性的动画曲线 */
  cursor: pointer;
  height: 100%; /* 确保卡片高度统一 */
  display: flex;
  flex-direction: column;
}

/* 增大卡片尺寸 */
.feature-card :deep(.ant-card-body) {
  padding: 24px;
  flex: 1;
  display: flex;
  flex-direction: column;
}

.feature-card :deep(.ant-card-meta) {
  flex: 1;
}

.card-icon {
  height: 160px; /* 增大图标区域高度 */
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 12px 12px 0 0;
  transition: all 0.3s ease;
}

.feature-card:hover {
  transform: scale(1.03); /* 减小放大比例避免内容溢出 */
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
  z-index: 1; /* 确保放大时覆盖其他元素 */
}

.feature-card:hover .card-icon {
  transform: none; /* 取消图标单独动画 */
}

/* 调整卡片内容间距 */
.feature-card :deep(.ant-card-meta-title) {
  font-size: 18px;
  margin-bottom: 8px;
}

.feature-card :deep(.ant-card-meta-description) {
  font-size: 14px;
}

/* 响应式调整 */
@media (max-width: 1200px) {
  .content-wrapper {
    max-width: 100%;
    padding: 24px;
  }
}

@media (max-width: 768px) {
  .content-wrapper {
    padding: 16px;
  }
  
  .feature-icon {
    font-size: 36px !important;
  }
  .card-icon {
    height: 140px;
  }
}
</style>