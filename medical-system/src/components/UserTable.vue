<template>
    <div>
        <!-- 添加表格工具栏 -->
        <div class="table-toolbar">
            <a-space>
                <a-button size="small" @click="$emit('add-user', userType)" v-if="showAddButton">
                    <plus-outlined /> 添加{{ userTypeLabel }}
                </a-button>
            </a-space>
        </div>

        <a-table :columns="columns" :data-source="data" :row-key="record => record.id" :pagination="{ pageSize: 10 }">
            <template #bodyCell="{ column, record }">
                <template v-if="column.key === 'action'">
                    <a-space>
                        <a-button type="link" size="small" @click="handleEdit(record)">
                            编辑
                        </a-button>
                        <a-button type="link" size="small" danger @click="handleDelete(record)">
                            删除
                        </a-button>
                    </a-space>
                </template>
                <template v-else-if="column.key === 'status'">
                    <a-tag :color="record.status === 'active' ? 'green' : 'red'">
                        {{ record.status === 'active' ? '活跃' : '禁用' }}
                    </a-tag>
                </template>
            </template>
        </a-table>

        <!-- 编辑用户模态框 -->
        <a-modal v-model:visible="editModalVisible" title="编辑用户信息" @ok="handleEditSubmit" @cancel="handleEditCancel">
            <a-form :model="editForm" layout="vertical">
                <a-form-item label="用户名">
                    <a-input v-model:value="editForm.name" />
                </a-form-item>
                <a-form-item label="账号">
                    <a-input v-model:value="editForm.account" />
                </a-form-item>
                <a-form-item label="密码">
                    <a-input-password v-model:value="editForm.password" />
                </a-form-item>
                <a-form-item label="手机号">
                    <a-input v-model:value="editForm.cellphone" />
                </a-form-item>
                <template v-if="userType === 'doctor'">
                    <a-form-item label="科室">
                        <a-select v-model:value="editForm.department">
                            <a-select-option value="内科">内科</a-select-option>
                            <a-select-option value="外科">外科</a-select-option>
                            <a-select-option value="儿科">儿科</a-select-option>
                            <a-select-option value="妇产科">妇产科</a-select-option>
                            <a-select-option value="眼科">眼科</a-select-option>
                        </a-select>
                    </a-form-item>
                    <a-form-item label="职称">
                        <a-select v-model:value="editForm.title">
                            <a-select-option value="专家医师">专家医师</a-select-option>
                            <a-select-option value="主任医师">主任医师</a-select-option>
                            <a-select-option value="副主任医师">副主任医师</a-select-option>
                            <a-select-option value="普通医师">普通医师</a-select-option>
                        </a-select>
                    </a-form-item>
                </template>
                <template v-if="userType === 'patient'">
                    <a-form-item label="性别">
                        <a-select v-model:value="editForm.gender">
                            <a-select-option value="男">男</a-select-option>
                            <a-select-option value="女">女</a-select-option>
                            <a-select-option value="其他">其他</a-select-option>
                        </a-select>
                    </a-form-item>
                    <a-form-item label="年龄">
                        <a-input-number v-model:value="editForm.age" :min="0" />
                    </a-form-item>
                </template>
                <a-form-item label="状态">
                    <a-select v-model:value="editForm.status">
                        <a-select-option value="active">活跃</a-select-option>
                        <a-select-option value="inactive">禁用</a-select-option>
                    </a-select>
                </a-form-item>
            </a-form>
        </a-modal>
    </div>
</template>

<script setup>
import { h } from 'vue';
import { ref, reactive, computed } from 'vue';
import { message, Modal } from 'ant-design-vue';
import { ExclamationCircleOutlined } from '@ant-design/icons-vue';
import { PlusOutlined } from '@ant-design/icons-vue';
import { delete_user, edit_user } from '@/api/api'; // 假设这里有 edit_user 方法

const props = defineProps({
  data: Array,
  userType: String,
  showAddButton: {
    type: Boolean,
    default: true
  }
});

const userTypeLabel = computed(() => {
  switch(props.userType) {
    case 'doctor': return '医生';
    case 'patient': return '患者';
    default: return '管理员';
  }
});

const emit = defineEmits(['refresh']);

const editModalVisible = ref(false);
const editForm = reactive({
    id: '',
    name: '',
    account: '',
    password: '',
    cellphone: '',
    department: '',
    title: '',
    gender: '男',
    age: 18,
    status: 'active'
});

const userType = computed(() => {
    if (props.data.some(user => user.department)) return 'doctor';
    if (props.data.some(user => user.gender)) return 'patient';
    return 'admin';
});

const columns = computed(() => {
    const baseColumns = [
        {
            title: 'ID',
            dataIndex: 'id',
            key: 'id'
        },
        {
            title: '用户名',
            dataIndex: 'name',
            key: 'name'
        },
        // {
        //     title: '账号',
        //     dataIndex: 'account',
        //     key: 'account'
        // },
        {
            title: '手机号',
            dataIndex: 'userCell',
            key: 'userCell'
        }
    ];

    if (userType.value === 'doctor') {
        baseColumns.push(
            {
                title: '科室',
                dataIndex: 'department',
                key: 'department'
            },
            // {
            //     title: '职称',
            //     dataIndex: 'title',
            //     key: 'title'
            // }
        );
    } else if (userType.value === 'patient') {
        baseColumns.push(
            {
                title: '性别',
                dataIndex: 'gender',
                key: 'gender'
            },
            {
                title: '年龄',
                dataIndex: 'age',
                key: 'age'
            }
        );
    } else {
        baseColumns.push({
            title: '角色',
            dataIndex: 'role',
            key: 'role'
        });
    }

    baseColumns.push(
        {
            title: '状态',
            dataIndex: 'status',
            key: 'status'
        },
        {
            title: '操作',
            key: 'action'
        }
    );

    return baseColumns;
});

const handleEdit = (record) => {
    Object.assign(editForm, record);
    editModalVisible.value = true;
};

const handleDelete = (record) => {
    Modal.confirm({
        title: '确认删除用户?',
        icon: h(ExclamationCircleOutlined),
        content: `确定要删除用户 ${record.name} 吗?`,
        okText: '确认',
        okType: 'danger',
        cancelText: '取消',
        async onOk() {
            const response = await delete_user(record.id);
            console.log(record.id);
            console.log(response);
            return new Promise((resolve, reject) => {
                message.success('用户删除成功');
                emit('refresh');
                resolve();
            });
        }
    });
};

const handleEditSubmit = async () => {
    try {
        const formData = new FormData();
        formData.append('user_id', editForm.id);
        if (editForm.name) formData.append('new_name', editForm.name);
        if (editForm.account) formData.append('new_acc', editForm.account);
        if (editForm.password) formData.append('new_psd', editForm.password);
        if (editForm.cellphone) formData.append('new_cell', editForm.cellphone);
        if (userType.value === 'doctor' && editForm.department) {
            formData.append('new_dep', editForm.department);
        }
        if (userType.value === 'doctor' && editForm.title) {
            formData.append('new_title', editForm.title);
        }
        if (userType.value === 'patient' && editForm.gender) {
            formData.append('new_gender', editForm.gender);
        }
        if (userType.value === 'patient' && editForm.age) {
            formData.append('new_age', editForm.age);
        }

        const response = await edit_user(formData);
        if (response) {
            message.success('用户信息更新成功');
            emit('refresh');
            editModalVisible.value = false;
        } else {
            message.error('更新用户信息失败: 服务器返回异常');
        }
    } catch (error) {
        console.error('更新用户信息失败:', error);
        if (error.response) {
            message.error('更新用户信息失败: ' + error.response.data.message);
        } else if (error.request) {
            message.error('更新用户信息失败: 没有收到服务器响应');
        } else {
            message.error('更新用户信息失败: ' + error.message);
        }
    }
};

const handleEditCancel = () => {
    editModalVisible.value = false;
};
</script>

<style scoped>
.table-toolbar {
  margin-bottom: 16px;
  display: flex;
  justify-content: space-between;
}
</style>    