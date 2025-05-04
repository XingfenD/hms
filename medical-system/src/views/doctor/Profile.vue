<template>
    <div class="profile-container">
        <a-card title="个人设置" bordered>
            <a-form :model="userInfo" @finish="handleSubmit">
                <a-form-item label="姓名" name="name">
                    <a-input v-model:value="userInfo.Username" />
                </a-form-item>
                <a-form-item label="邮箱" name="email">
                    <a-input v-model:value="userInfo.email" />
                </a-form-item>
                <a-form-item label="手机号码" name="phone">
                    <a-input v-model:value="userInfo.UserCell" />
                </a-form-item>
                <a-form-item>
                    <a-button type="primary" html-type="submit">保存设置</a-button>
                    <a-button style="margin-left: 8px" @click="resetForm">重置</a-button>
                </a-form-item>
            </a-form>
        </a-card>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { message } from 'ant-design-vue';
import { getLoginInfo, get_single_userinfo_data } from '@/api/api' // 引入接口

// 用户信息
const userInfo = ref({})

// 保存初始数据，用于重置
const initialUserInfo = ref({})

// 获取登录用户信息
const fetchUserInfo = async () => {
    try {
        const loginInfo = await getLoginInfo()
        const userId = loginInfo.data.data.user_id
        const userData = await get_single_userinfo_data(userId)
        userInfo.value = userData.data.data[0]
        initialUserInfo.value = { ...userInfo.value }
    } catch (error) {
        message.error('获取用户信息失败，请稍后重试')
    }
}

// 处理表单提交
const handleSubmit = () => {
    // 这里可以添加保存数据到后端的逻辑
    message.success('设置保存成功');
};

// 重置表单
const resetForm = () => {
    userInfo.value = { ...initialUserInfo.value };
    message.info('表单已重置');
};

onMounted(async () => {
    await fetchUserInfo()
})
</script>

<style scoped>
/* .profile-container {
    padding: 24px;
    background-color: #f0f2f5;
  } */

/* .ant-card {
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
  }
   */
.ant-form-item {
    margin-bottom: 16px;
}
</style>