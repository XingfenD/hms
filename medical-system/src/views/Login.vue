<template>
  <div class="login-container">
    <!-- 新增背景层 -->
    <div class="background-layer">
      <div class="particles"></div>
      <div class="medical-pattern"></div>
    </div>
    <div class="login-background">
      <div class="login-left-panel">
        <div class="welcome-message">
          <h1>SmartHosp+</h1>
          <p class="subtitle">科技赋能医疗 · 智慧守护健康</p>
          <div class="medical-icon">
            <svg width="120" height="120" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="#1890ff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M12 8V16" stroke="#1890ff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M8 12H16" stroke="#1890ff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M16.5 7.5C16.5 7.5 18 9 18 12C18 15 16.5 16.5 16.5 16.5" stroke="#1890ff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M7.5 7.5C7.5 7.5 6 9 6 12C6 15 7.5 16.5 7.5 16.5" stroke="#1890ff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M7.5 16.5C7.5 16.5 9 18 12 18C15 18 16.5 16.5 16.5 16.5" stroke="#1890ff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M7.5 7.5C7.5 7.5 9 6 12 6C15 6 16.5 7.5 16.5 7.5" stroke="#1890ff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
        </div>
      </div>
      <div class="login-right-panel">
        <a-card class="login-card" :bordered="false">
          <template v-if="isLogin">
            <h2 class="login-title">用户登录</h2>
            <p class="login-subtitle">请输入您的账号和密码</p>
            <a-form
              :model="formState"
              name="normal_login"
              class="login-form"
              @finish="onFinish"
              @finishFailed="onFinishFailed"
            >
              <!-- 修改为下拉选择框 -->
              <a-form-item
                name="role"
                :rules="[{ required: true, message: '请选择登录身份!' }]"
              >
                <a-select
                  v-model:value="formState.role"
                  size="large"
                  placeholder="请选择身份"
                  class="role-selector"
                >
                  <a-select-option value="医生">
                    <span class="role-option">
                      <UserOutlined /> 医生
                    </span>
                  </a-select-option>
                  <a-select-option value="admin">
                    <span class="role-option">
                      <LockOutlined /> 管理员
                    </span>
                  </a-select-option>
                  <a-select-option value="患者">
                    <span class="role-option">
                      <TeamOutlined /> 患者
                    </span>
                  </a-select-option>
                  <!-- <a-select-option value="药房管理员">
                    <span class="role-option">
                      <MedicineBoxOutlined /> 药房管理员
                    </span>
                  </a-select-option> -->
                </a-select>
              </a-form-item>
              <a-form-item
                name="username"
                :rules="[{ required: true, message: '请输入用户名!' }]"
              >
                <a-input v-model:value="formState.username" size="large" placeholder="用户名/手机号">
                  <template #prefix>
                    <UserOutlined class="site-form-item-icon" />
                  </template>
                </a-input>
              </a-form-item>
              <a-form-item
                name="password"
                :rules="[{ required: true, message: '请输入密码!' }]"
              >
                <a-input-password v-model:value="formState.password" size="large" placeholder="密码">
                  <template #prefix>
                    <LockOutlined class="site-form-item-icon" />
                  </template>
                </a-input-password>
              </a-form-item>
              <a-form-item>
                <a-form-item name="remember" no-style>
                  <a-checkbox v-model:checked="formState.remember">记住我</a-checkbox>
                </a-form-item>
                <a class="login-form-forgot" href="">忘记密码</a>
              </a-form-item>
              <a-form-item>
                <a-button :loading="loading" type="primary" html-type="submit" size="large" block>
                  登录
                </a-button>
              </a-form-item>
            </a-form>
            <div class="login-other">
              <span>其他登录方式</span>
              <a-divider type="vertical" />
              <a-tooltip title="微信登录">
                <WechatOutlined class="login-icon" />
              </a-tooltip>
              <a-divider type="vertical" />
              <a-tooltip title="支付宝登录">
                <AlipayOutlined class="login-icon" />
              </a-tooltip>
              <a-divider type="vertical" />
              <a-tooltip title="短信验证码登录">
                <MobileOutlined class="login-icon" />
              </a-tooltip>
            </div>
            <div class="login-register">
              还没有账号？<a @click="toggleForm">立即注册</a>
            </div>
          </template>
          <template v-else>
            <h2 class="login-title">用户注册</h2>
            <p class="login-subtitle">请输入注册信息</p>
            <a-form
              :model="registerFormState"
              name="register_form"
              class="login-form"
              @finish="onRegisterFinish"
              @finishFailed="onRegisterFinishFailed"
            >
              <!-- 新增姓名输入项 -->
              <a-form-item
                name="name"
                :rules="[{ required: true, message: '请输入姓名!' }]"
              >
                <a-input v-model:value="registerFormState.name" size="large" placeholder="姓名">
                  <template #prefix>
                    <UserOutlined class="site-form-item-icon" />
                  </template>
                </a-input>
              </a-form-item>
              <!-- 移除角色选择框 -->
              <a-form-item
                name="username"
                :rules="[{ required: true, message: '请输入用户名!' }, { validator: validateUsername }]"
              >
                <a-input v-model:value="registerFormState.username" size="large" placeholder="用户名">
                  <template #prefix>
                    <UserOutlined class="site-form-item-icon" />
                  </template>
                </a-input>
              </a-form-item>
              <a-form-item
                name="phone"
                :rules="[{ required: true, message: '请输入手机号!' }, { pattern: /^1[3-9]\d{9}$/, message: '请输入有效的手机号' }]"
              >
                <a-input v-model:value="registerFormState.phone" size="large" placeholder="手机号">
                  <template #prefix>
                    <MobileOutlined class="site-form-item-icon" />
                  </template>
                </a-input>
              </a-form-item>
              <a-form-item
                name="gender"
                :rules="[{ required: true, message: '请选择性别!' }]"
              >
                <a-select
                  v-model:value="registerFormState.gender"
                  size="large"
                  placeholder="请选择性别"
                >
                  <a-select-option value="男">
                    <span class="role-option">
                      <i class="fa-solid fa-male"></i> 男
                    </span>
                  </a-select-option>
                  <a-select-option value="女">
                    <span class="role-option">
                      <i class="fa-solid fa-female"></i> 女
                    </span>
                  </a-select-option>
                </a-select>
              </a-form-item>
              <a-form-item
                name="age"
                :rules="[{ required: true, message: '请输入年龄!' }, { pattern: /^\d+$/, message: '请输入有效的年龄' }]"
              >
                <a-input v-model:value="registerFormState.age" size="large" placeholder="年龄">
                  <template #prefix>
                    <UserOutlined class="site-form-item-icon" />
                  </template>
                </a-input>
              </a-form-item>
              <a-form-item
                name="password"
                :rules="[{ required: true, message: '请输入密码!' }]"
              >
                <a-input-password v-model:value="registerFormState.password" size="large" placeholder="密码">
                  <template #prefix>
                    <LockOutlined class="site-form-item-icon" />
                  </template>
                </a-input-password>
              </a-form-item>
              <a-form-item
                name="confirmPassword"
                :rules="[{ required: true, message: '请再次输入密码!' }, { validator: validateConfirmPassword }]"
              >
                <a-input-password v-model:value="registerFormState.confirmPassword" size="large" placeholder="确认密码">
                  <template #prefix>
                    <LockOutlined class="site-form-item-icon" />
                  </template>
                </a-input-password>
              </a-form-item>
              <a-form-item>
                <a-button :loading="registerLoading" type="primary" html-type="submit" size="large" block>
                  注册
                </a-button>
              </a-form-item>
            </a-form>
            <div class="login-register">
              已有账号？<a @click="toggleForm">立即登录</a>
            </div>
          </template>
        </a-card>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import {
  UserOutlined,
  LockOutlined,
  WechatOutlined,
  AlipayOutlined,
  MobileOutlined,
  TeamOutlined,
  MedicineBoxOutlined
} from '@ant-design/icons-vue';
import { message } from 'ant-design-vue';
import { getLoginInfo, login, register } from '../api/api.js';

const router = useRouter();
const isLogin = ref(true);
const formState = reactive({
  role: '患者', // 默认选中患者
  username: '',
  password: '',
  remember: true,
});
// 新增姓名属性
const registerFormState = reactive({
  name: '',
  role: 'patient', // 注册角色固定为患者
  username: '',
  phone: '',
  gender: '',
  age: '',
  password: '',
  confirmPassword: ''
});
const loading = ref(false);
const registerLoading = ref(false);

const onFinish = async (values) => {
  try {
    loading.value = true;
    // console.log(values.username, values.password, values.role);
    const response = await login(values.username, values.password, values.role);
    if (response.code === 200) {
      message.success(`${getRoleName(values.role)}登录成功！`);
      const loginfo = await getLoginInfo();
      console.log(loginfo.data);

      // 根据用户角色进行页面跳转
      if (values.role === 'admin') {
        router.push('/admin');
      } else if (values.role === '医生') {
        router.push('/doctor');
      } else if (values.role === '患者') {
        router.push('/patient');
      } else if (values.role === '药房管理员') {
        router.push('/drugadmin');
      }
    } else {
      message.error(response.message);
    }
  } catch (error) {
    message.error('登录请求出错，请稍后重试');
  } finally {
    loading.value = false;
  }
};

const onRegisterFinish = async (values) => {
  try {
    registerLoading.value = true;
    // 确保注册时角色为患者
    values.role = 'patient';
    // 新增传递姓名参数
    const response = await register(values.username, values.name, values.password, values.phone, values.gender, values.age);
    console.log(values.name, values.username, values.password, values.role, values.phone, values.gender, values.age);
    if (response.code === 200) {
      message.success(`${getRoleName(values.role)}注册成功，请登录！`);
      isLogin.value = true;
    } else {
      message.error(response.message);
    }
  } catch (error) {
    message.error('注册请求出错，请稍后重试');
  } finally {
    registerLoading.value = false;
  }
};

const getRoleName = (role) => {
  const roles = {
    doctor: '医生',
    admin: '管理员',
    patient: '患者'
  };
  return roles[role] || '用户';
};

const onFinishFailed = (errorInfo) => {
  console.log('Failed:', errorInfo);
};

const onRegisterFinishFailed = (errorInfo) => {
  console.log('注册失败:', errorInfo);
};

const toggleForm = () => {
  isLogin.value = !isLogin.value;
};

const validateConfirmPassword = (rule, value) => {
  if (value === registerFormState.password) {
    return Promise.resolve();
  }
  return Promise.reject('两次输入的密码不一致');
};

const validateUsername = (rule, value) => {
  if (/^\d/.test(value)) {
    return Promise.reject('用户名不能以数字开头');
  }
  return Promise.resolve();
};
</script>

<style scoped>
.login-container {
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background: #f0f2f5;
}

/* 新增身份选择器样式 */
.role-selector {
  width: 100%;
}

.role-selector .ant-select-selector {
  height: 40px !important;
  display: flex !important;
  align-items: center !important;
}

.role-option {
  display: flex;
  align-items: center;
  gap: 8px;
}

/* 调整表单间距 */
.login-form .ant-form-item {
  margin-bottom: 16px;
}

/* 新增背景样式 */
.background-layer {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 0;
  background: linear-gradient(135deg, #e6f7ff 0%, #bae7ff 100%);
}

.medical-pattern {
  position: absolute;
  width: 100%;
  height: 100%;
  background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M50 30L20 50L50 70L80 50L50 30Z' fill='none' stroke='%23ffffff' stroke-width='0.5' stroke-opacity='0.2'/%3E%3Cpath d='M30 50L50 20L70 50L50 80L30 50Z' fill='none' stroke='%23ffffff' stroke-width='0.5' stroke-opacity='0.2'/%3E%3C/svg%3E");
  opacity: 0.3;
}

.particles {
  position: absolute;
  width: 100%;
  height: 100%;
  background-image: radial-gradient(circle at 20% 30%, rgba(255,255,255,0.8) 0px, transparent 1px),
    radial-gradient(circle at 80% 70%, rgba(255,255,255,0.6) 0px, transparent 1px);
  background-size: 30px 30px;
}


/* 调整原有样式 */
.login-background {
  width: 85%;
  height: 85%;
  display: flex;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
  border-radius: 12px;
  overflow: hidden;
  background: white;
  position: relative;
  z-index: 1;
}

.login-left-panel {
  flex: 1;
  background: linear-gradient(135deg, #1890ff 0%, #096dd9 100%);
  color: white;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 40px;
  position: relative;
  overflow: hidden;
}

.login-left-panel::before {
  content: '';
  position: absolute;
  top: -50%;
  right: -50%;
  width: 100%;
  height: 100%;
  background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
}

.welcome-message {
  text-align: center;
}

.welcome-message h1 {
  font-size: 2.5rem;
  margin-bottom: 1rem;
  font-weight: 500;
}

.subtitle {
  font-size: 1.1rem;
  opacity: 0.9;
  margin-bottom: 2rem;
}

.medical-icon {
  margin-top: 2rem;
}

.login-right-panel {
  flex: 1;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 40px;
}

.login-card {
  width: 100%;
  max-width: 400px;
  border: none !important;
  box-shadow: none !important;
}

.login-title {
  text-align: center;
  font-size: 1.8rem;
  margin-bottom: 0.5rem;
  color: #1890ff;
}

.login-subtitle {
  text-align: center;
  color: rgba(0, 0, 0, 0.45);
  margin-bottom: 2rem;
}

.login-form-forgot {
  float: right;
}

.login-form-button {
  width: 100%;
}

.login-other {
  margin-top: 24px;
  text-align: center;
  color: rgba(0, 0, 0, 0.45);
}

.login-icon {
  color: rgba(0, 0, 0, 0.45);
  font-size: 1.2rem;
  margin: 0 4px;
  cursor: pointer;
  transition: color 0.3s;
}

.login-icon:hover {
  color: #1890ff;
}

.login-register {
  margin-top: 24px;
  text-align: center;
}

@media (max-width: 768px) {
  .login-background {
    width: 95%;
    height: 90%;
  }

  .login-left-panel::before {
    display: none;
  }
}
</style> 