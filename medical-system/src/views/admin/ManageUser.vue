<template>
    <a-layout class="user-management">
        <!-- 顶部导航 -->
        <a-page-header title="用户管理" sub-title="管理系统所有用户账户" class="page-header" @back="() => router.push('/admin')">
            <template #backIcon>
                <arrow-left-outlined />
            </template>
            <template #breadcrumb>
                <a-breadcrumb>
                    <a-breadcrumb-item>
                        <router-link to="/admin">管理员面板</router-link>
                    </a-breadcrumb-item>
                    <a-breadcrumb-item>用户管理</a-breadcrumb-item>
                </a-breadcrumb>
            </template>
            <template #extra>
                <a-space>
                    <a-button type="primary" @click="showAddModal">
                        <template #icon><plus-outlined /></template>
                        添加用户
                    </a-button>
                    <a-button @click="showImportModal">
                        <template #icon><upload-outlined /></template>
                        批量导入
                    </a-button>
                </a-space>
            </template>
        </a-page-header>

        <!-- 主内容区 -->
        <a-layout-content class="content">
            <div class="container">
                <!-- 用户统计卡片 -->
                <a-row :gutter="16" class="stats-row">
                    <a-col :xs="24" :sm="12" :md="8">
                        <a-card hoverable class="stat-card" :body-style="{ padding: '20px' }">
                            <a-statistic title="医生用户" :value="doctorCount" :precision="0" class="doctor-stat">
                                <template #prefix>
                                    <user-outlined />
                                </template>
                                <template #suffix>
                                    <a-tag color="blue" v-if="doctorGrowth > 0">
                                        <arrow-up-outlined /> {{ doctorGrowth }}%
                                    </a-tag>
                                    <a-tag color="red" v-else-if="doctorGrowth < 0">
                                        <arrow-down-outlined /> {{ Math.abs(doctorGrowth) }}%
                                    </a-tag>
                                </template>
                            </a-statistic>
                        </a-card>
                    </a-col>
                    <a-col :xs="24" :sm="12" :md="8">
                        <a-card hoverable class="stat-card" :body-style="{ padding: '20px' }">
                            <a-statistic title="患者用户" :value="patientCount" :precision="0" class="patient-stat">
                                <template #prefix>
                                    <team-outlined />
                                </template>
                                <template #suffix>
                                    <a-tag color="blue" v-if="patientGrowth > 0">
                                        <arrow-up-outlined /> {{ patientGrowth }}%
                                    </a-tag>
                                    <a-tag color="red" v-else-if="patientGrowth < 0">
                                        <arrow-down-outlined /> {{ Math.abs(patientGrowth) }}%
                                    </a-tag>
                                </template>
                            </a-statistic>
                        </a-card>
                    </a-col>
                    <a-col :xs="24" :sm="12" :md="8">
                        <a-card hoverable class="stat-card" :body-style="{ padding: '20px' }">
                            <a-statistic title="管理员" :value="adminCount" :precision="0" class="admin-stat">
                                <template #prefix>
                                    <crown-outlined />
                                </template>
                            </a-statistic>
                        </a-card>
                    </a-col>
                </a-row>

                <!-- 搜索和筛选 -->
                <div class="search-filter">
                    <a-input v-model:value="searchText" placeholder="搜索用户名、手机号等" @change="filterUsers" allow-clear
                        style="width: 200px" />
                    <a-select v-model:value="filterType" placeholder="筛选用户类型" @change="filterUsers" style="width: 150px"
                        allow-clear>
                        <a-select-option value="">全部</a-select-option>
                        <a-select-option value="doctor">医生</a-select-option>
                        <a-select-option value="patient">患者</a-select-option>
                        <a-select-option value="admin">管理员</a-select-option>
                    </a-select>
                </div>

                <!-- 用户表格卡片 -->
                <a-card class="user-table-card">
                    <div class="table-header">
                        <a-tabs v-model:activeKey="activeTab" type="card">
                            <a-tab-pane key="doctor" tab="医生用户">
                                <user-table :data="filteredDoctorData" user-type="doctor" @refresh="fetchUsers"
                                    @add-user="showAddModal" :showAddButton="true" />
                            </a-tab-pane>
                            <a-tab-pane key="patient" tab="患者用户">
                                <user-table :data="filteredPatientData" user-type="patient" @refresh="fetchUsers"
                                    @add-user="showAddModal" :showAddButton="true" />
                            </a-tab-pane>
                            <a-tab-pane key="admin" tab="管理员">
                                <user-table :data="filteredAdminData" user-type="admin" @refresh="fetchUsers"
                                    @add-user="showAddModal" :showAddButton="true" />
                            </a-tab-pane>
                        </a-tabs>
                    </div>
                </a-card>
            </div>
        </a-layout-content>

        <!-- 添加用户模态框 -->
        <a-modal v-model:visible="addModalVisible" :title="`添加${currentUserTypeLabel}用户`" width="800px"
            :mask-closable="false" :destroy-on-close="true" @ok="handleAddUser" @cancel="resetAddForm">
            <a-form ref="addFormRef" :model="addForm" :rules="addRules" layout="vertical">
                <a-row :gutter="24">
                    <a-col :span="12">
                        <a-form-item label="手机号" name="cellphone">
                            <a-input v-model:value="addForm.cellphone" placeholder="请输入手机号" />
                        </a-form-item>
                    </a-col>
                    <a-col :span="12">
                        <a-form-item label="用户名" name="name">
                            <a-input v-model:value="addForm.name" placeholder="请输入用户名" />
                        </a-form-item>
                    </a-col>
                </a-row>

                <a-row :gutter="24">
                    <a-col :span="12">
                        <a-form-item label="账号" name="account">
                            <a-input v-model:value="addForm.account" placeholder="请输入账号" />
                        </a-form-item>
                    </a-col>
                    <a-col :span="12">
                        <a-form-item label="密码" name="password">
                            <a-input-password v-model:value="addForm.password" placeholder="请输入密码" />
                        </a-form-item>
                    </a-col>
                </a-row>

                <a-row :gutter="24">
                    <a-col :span="12">
                        <a-form-item label="用户类型" name="userType">
                            <a-select v-model:value="addForm.userType" @change="handleUserTypeChange"
                                placeholder="请选择用户类型">
                                <a-select-option value="doctor">医生</a-select-option>
                                <a-select-option value="patient">患者</a-select-option>
                                <a-select-option value="admin">管理员</a-select-option>
                            </a-select>
                        </a-form-item>
                    </a-col>
                    <a-col :span="12">
                        <a-form-item label="性别" name="gender">
                            <a-select v-model:value="addForm.gender" placeholder="请选择性别">
                                <a-select-option value="男">男</a-select-option>
                                <a-select-option value="女">女</a-select-option>
                                <a-select-option value="其他">其他</a-select-option>
                            </a-select>
                        </a-form-item>
                    </a-col>
                </a-row>

                <a-row :gutter="24">
                    <a-col :span="12">
                        <a-form-item label="年龄" name="age">
                            <a-input-number v-model:value="addForm.age" :min="0" :max="120" placeholder="请输入年龄"
                                style="width: 100%" />
                        </a-form-item>
                    </a-col>
                </a-row>

                <template v-if="addForm.userType === 'doctor'">
                    <a-divider orientation="left">医生信息</a-divider>
                    <a-row :gutter="24">
                        <a-col :span="12">
                            <a-form-item label="科室" name="doc_dep">
                                <a-select v-model:value="addForm.doc_dep" placeholder="请选择科室">
                                    <a-select-option value="内科">内科</a-select-option>
                                    <a-select-option value="外科">外科</a-select-option>
                                    <a-select-option value="儿科">儿科</a-select-option>
                                    <a-select-option value="妇产科">妇产科</a-select-option>
                                    <a-select-option value="眼科">眼科</a-select-option>
                                </a-select>
                            </a-form-item>
                        </a-col>
                        <a-col :span="12">
                            <a-form-item label="职称" name="doc_title">
                                <a-select v-model:value="addForm.doc_title" placeholder="请选择职称">
                                    <a-select-option value="专家医师">专家医师</a-select-option>
                                    <a-select-option value="主任医师">主任医师</a-select-option>
                                    <a-select-option value="副主任医师">副主任医师</a-select-option>
                                    <a-select-option value="普通医师">普通医师</a-select-option>
                                </a-select>
                            </a-form-item>
                        </a-col>
                    </a-row>
                </template>
            </a-form>
        </a-modal>

        <!-- 批量导入模态框 -->
        <a-modal v-model:visible="importModalVisible" title="批量导入用户" width="600px" :mask-closable="false"
            @ok="handleImport" @cancel="handleImportCancel">
            <a-upload-dragger v-model:fileList="fileList" name="file" :multiple="false" :before-upload="beforeUpload"
                accept=".xlsx,.xls,.csv">
                <p class="ant-upload-drag-icon">
                    <inbox-outlined />
                </p>
                <p class="ant-upload-text">点击或拖拽文件到此处上传</p>
                <p class="ant-upload-hint">
                    支持Excel/CSV文件，<a href="/templates/users_import_template.xlsx">下载模板</a>
                </p>
            </a-upload-dragger>
            <div class="import-progress" v-if="importProgress > 0">
                <a-progress :percent="importProgress" status="active" />
                <p>正在导入: {{ importProgress }}%</p>
            </div>
        </a-modal>
    </a-layout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import {
    UserOutlined,
    TeamOutlined,
    CrownOutlined,
    PlusOutlined,
    UploadOutlined,
    InboxOutlined,
    ArrowLeftOutlined,
    ArrowUpOutlined,
    ArrowDownOutlined
} from '@ant-design/icons-vue';
import { message, Modal } from 'ant-design-vue';
import UserTable from '@/components/UserTable.vue';
import { get_users_data, add_user } from '@/api/api';

const router = useRouter();

// 用户数据
const doctorData = ref([]);
const patientData = ref([]);
const adminData = ref([]);
const doctorCount = ref(0);
const patientCount = ref(0);
const adminCount = ref(0);
const doctorGrowth = ref(5.2); // 模拟增长率
const patientGrowth = ref(12.8); // 模拟增长率

// 搜索和标签页
const searchText = ref('');
const filterType = ref('');
const activeTab = ref('doctor');

// 添加用户表单
const addFormRef = ref();
const addForm = reactive({
    account: '',
    name: '',
    password: '',
    userType: 'doctor',
    cellphone: '',
    gender: '男',
    age: 18,
    doc_dep: '',
    doc_title: ''
});

const addRules = {
    account: [
        { required: true, message: '请输入账号' }
    ],
    name: [
        { required: true, message: '请输入用户名' },
        { min: 2, max: 20, message: '用户名长度2-20个字符' }
    ],
    password: [
        { required: true, message: '请输入密码' },
        { min: 6, message: '密码至少6位' }
    ],
    userType: [{ required: true, message: '请选择用户类型' }],
    cellphone: [
        { pattern: /^1[3-9]\d{9}$/, message: '请输入正确的手机号' }
    ],
    gender: [
        { required: true, message: '请选择性别' }
    ],
    age: [
        { required: true, message: '请输入年龄' }
    ],
    doc_dep: [{ required: true, message: '请选择科室', trigger: 'change' }],
    doc_title: [{ required: true, message: '请选择职称', trigger: 'change' }]
};

const addingUser = ref(false);
const addModalVisible = ref(false);

// 批量导入
const importModalVisible = ref(false);
const fileList = ref([]);
const importProgress = ref(0);

const tabs = [
    { key: 'doctor', label: '医生' },
    { key: 'patient', label: '患者' },
    { key: 'admin', label: '管理员' }
];

const currentUserTypeLabel = computed(() => {
    const tab = tabs.find(t => t.key === addForm.userType);
    return tab ? tab.label : '';
});


const updateCounts = () => {
    doctorCount.value = doctorData.value.length;
    patientCount.value = patientData.value.length;
    adminCount.value = adminData.value.length;
};

const showAddModal = () => {
    addForm.userType = activeTab.value;
    addModalVisible.value = true;
};

const resetAddForm = () => {
    addFormRef.value?.resetFields();
    Object.assign(addForm, {
        account: '',
        name: '',
        password: '',
        userType: activeTab.value,
        cellphone: '',
        gender: '男',
        age: 18,
        doc_dep: '',
        doc_title: ''
    });
};

const handleAddUser = async () => {
    try {
        await addFormRef.value.validate();
        addingUser.value = true;

        const formData = new FormData();
        if (addForm.userType === 'doctor') {
            formData.append('user_type', '医生');
        } else if(addForm.userType === 'patient') {
            formData.append('user_type', '患者');
        } else {
            formData.append('user_type', addForm.userType);
        }

        formData.append('name', addForm.name);
        formData.append('account', addForm.account);
        formData.append('password', addForm.password);
        formData.append('cellphone', addForm.cellphone);
        formData.append('gender', addForm.gender);
        formData.append('age', addForm.age);

        if (addForm.userType === 'doctor') {
            formData.append('doc_title', addForm.doc_title);
            formData.append('doc_dep', addForm.doc_dep);
        }

        const response = await add_user(formData);
        console.log(response, formData.get('user_type'))
        if (response.data) {
            fetchUsers();
            updateCounts();
            filterUsers();
            message.success('用户添加成功');
            addModalVisible.value = false;
            resetAddForm();
        } else {
            message.error('添加用户失败: 服务器返回异常');
        }
    } catch (error) {
        console.error('添加用户失败:', error);
        if (error.response) {
            message.error('添加用户失败: ' + error.response.data.message);
        } else if (error.request) {
            message.error('添加用户失败: 没有收到服务器响应');
        } else {
            message.error('添加用户失败: ' + error.message);
        }
    } finally {
        addingUser.value = false;
    }
}

const handleUserTypeChange = (value) => {
    console.log('用户类型变更:', value);
};

// 批量导入相关方法
const showImportModal = () => {
    importModalVisible.value = true;
};

const beforeUpload = (file) => {
    const isExcel = file.type === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' ||
        file.type === 'application/vnd.ms-excel';
    const isCSV = file.type === 'text/csv';

    if (!isExcel && !isCSV) {
        message.error('只能上传Excel或CSV文件!');
        return false;
    }

    const isLt10M = file.size / 1024 / 1024 < 10;
    if (!isLt10M) {
        message.error('文件大小不能超过10MB!');
        return false;
    }

    return false; // 返回false手动处理上传
};

const handleImport = async () => {
    if (fileList.value.length === 0) {
        message.warning('请先选择文件');
        return;
    }

    try {
        importProgress.value = 0;
        const interval = setInterval(() => {
            importProgress.value += 10;
            if (importProgress.value >= 100) {
                clearInterval(interval);
                setTimeout(() => {
                    message.success('导入成功!');
                    fetchUsers();
                    importModalVisible.value = false;
                    fileList.value = [];
                    importProgress.value = 0;
                }, 500);
            }
        }, 300);
    } catch (error) {
        message.error('导入失败: ' + error.message);
        importProgress.value = 0;
    }
};

const handleImportCancel = () => {
    fileList.value = [];
    importProgress.value = 0;
};

// 过滤后的数据 - 初始化为全部数据
const filteredDoctorData = ref([]);
const filteredPatientData = ref([]);
const filteredAdminData = ref([]);

// 获取用户数据后初始化过滤数据
const fetchUsers = async () => {
    try {
        const response = await get_users_data();
        const data = response.data.data;

        // 检查 doctors 数据是否存在
        doctorData.value = data.doctors ? data.doctors.map(doctor => ({
            id: doctor.DoctorID,
            name: doctor.FullName,
            department: doctor.Department,
            userCell: doctor.UserCell,
            status: 'active',
            lastLogin: new Date().toISOString().split('T')[0]
        })) : [];

        // 检查 patients 数据是否存在
        patientData.value = data.patients ? data.patients.map(patient => ({
            id: patient.PatientID,
            name: patient.FullName,
            gender: (patient.Gender == 0 ? '女':'男'),
            age: patient.Age,
            userCell: patient.UserCell,
            status: 'active',
            lastLogin: new Date().toISOString().split('T')[0]
        })) : [];

        // 检查 admins 数据是否存在
        adminData.value = data.admins ? data.admins.map(admin => ({
            id: admin.UserID,
            name: admin.Username,
            userCell: admin.UserCell,
            role: '管理员',
            status: 'active',
            lastLogin: new Date().toISOString().split('T')[0]
        })) : [];

        updateCounts();
        // 初始化时显示所有数据
        filteredDoctorData.value = [...doctorData.value];
        filteredPatientData.value = [...patientData.value];
        filteredAdminData.value = [...adminData.value];
    } catch (error) {
        message.error('获取用户数据失败: ' + error.message);
    }
};

// 修改筛选函数
const filterUsers = () => {
    const search = searchText.value.toLowerCase();

    // 总是对所有数据进行筛选，不管当前激活的标签页是什么
    filteredDoctorData.value = doctorData.value.filter(user =>
        user.name.toLowerCase().includes(search) ||
        user.department?.toLowerCase().includes(search) ||
        user.userCell?.toLowerCase().includes(search) ||
        user.status?.toLowerCase().includes(search) ||
        user.lastLogin?.toLowerCase().includes(search)
    );

    filteredPatientData.value = patientData.value.filter(user =>
        user.name.toLowerCase().includes(search) ||
        user.gender?.toLowerCase().includes(search) ||
        user.phone?.toLowerCase().includes(search) ||
        user.age.toString().includes(search) ||
        user.status?.toLowerCase().includes(search) ||
        user.lastLogin?.toLowerCase().includes(search)
    );

    filteredAdminData.value = adminData.value.filter(user =>
        user.name.toLowerCase().includes(search) ||
        user.userCell?.toLowerCase().includes(search) ||
        user.role?.toLowerCase().includes(search) ||
        user.status?.toLowerCase().includes(search) ||
        user.lastLogin?.toLowerCase().includes(search)
    );

    // 如果设置了用户类型筛选，则进一步过滤
    if (filterType.value) {
        if (filterType.value === 'doctor') {
            filteredPatientData.value = [];
            filteredAdminData.value = [];
        } else if (filterType.value === 'patient') {
            filteredDoctorData.value = [];
            filteredAdminData.value = [];
        } else if (filterType.value === 'admin') {
            filteredDoctorData.value = [];
            filteredPatientData.value = [];
        }
    }
};

// 初始化获取数据
onMounted(() => {
    fetchUsers();
});
</script>

<style scoped>
.user-management {
    min-height: 100vh;
    /* background: url('@/assets/images/medical-bg-pattern.png') no-repeat center center fixed; */
    background-size: cover;
}

.page-header {
    background: rgba(255, 255, 255, 0.95);
    padding: 16px 24px;
    margin-bottom: 1px;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
}

.content {
    padding: 24px;
    background: rgba(240, 242, 245, 0.9);
}

.container {
    max-width: 1400px;
    margin: 0 auto;
}

.stats-row {
    margin-bottom: 24px;
}

.stat-card {
    border-radius: 8px;
    transition: all 0.3s;
    background: rgba(255, 255, 255, 0.95);
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
}

.doctor-stat :deep(.ant-statistic-content) {
    color: #1890ff;
}

.patient-stat :deep(.ant-statistic-content) {
    color: #52c41a;
}

.admin-stat :deep(.ant-statistic-content) {
    color: #722ed1;
}

.user-table-card {
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.95);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.table-header {
    margin-bottom: 16px;
}

.import-progress {
    margin-top: 16px;
    text-align: center;
}

.search-filter {
    display: flex;
    gap: 16px;
    margin-bottom: 16px;
}

@media (max-width: 768px) {
    .content {
        padding: 12px;
    }

    .stats-row .ant-col {
        margin-bottom: 16px;
    }

    .page-header {
        padding: 12px;
    }
}
</style>    