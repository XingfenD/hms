<template>
    <a-layout class="database-management">
        <!-- 顶部导航 -->
        <a-page-header
            title="数据库管理"
            sub-title="数据库备份与恢复"
            class="page-header"
            @back="() => router.push('/admin')"
        >
            <template #backIcon>
                <arrow-left-outlined />
            </template>
            <template #breadcrumb>
                <a-breadcrumb>
                    <a-breadcrumb-item>
                        <router-link to="/admin">管理员面板</router-link>
                    </a-breadcrumb-item>
                    <a-breadcrumb-item>数据库管理</a-breadcrumb-item>
                </a-breadcrumb>
            </template>
        </a-page-header>
  
        <!-- 主内容区 -->
        <a-layout-content class="content">
            <div class="container">
                <!-- 备份恢复卡片 -->
                <a-card title="数据库操作" class="database-card">
                    <a-tabs v-model:activeKey="activeTab">
                        <!-- 备份标签页 -->
                        <a-tab-pane key="backup" tab="数据库备份">
                            <a-alert
                                message="数据库备份说明"
                                description="备份操作将导出当前数据库的所有数据到SQL文件，建议定期执行备份操作以确保数据安全。"
                                type="info"
                                show-icon
                                style="margin-bottom: 20px"
                            />
  
                            <a-form layout="vertical">
                                <a-form-item label="备份文件名称">
                                    <a-input
                                        v-model:value="backupFileName"
                                        placeholder="自动生成备份文件名"
                                        disabled
                                    />
                                </a-form-item>
  
                                <a-form-item label="备份路径">
                                    <a-input
                                        v-model:value="backupPath"
                                        placeholder="请输入备份文件保存路径"
                                        style="margin-bottom: 10px"
                                    />
                                    <a-typography-text type="secondary">
                                        默认路径: {{ defaultBackupPath }}
                                    </a-typography-text>
                                </a-form-item>
  
                                <a-form-item>
                                    <a-button
                                        type="primary"
                                        @click="handleBackup"
                                        :loading="backupLoading"
                                    >
                                        <template #icon><download-outlined /></template>
                                        执行备份
                                    </a-button>
                                </a-form-item>
                            </a-form>
  
                            <a-divider />
  
                            <h3>最近备份记录</h3>
                            <a-table
                                :columns="backupColumns"
                                :data-source="backupHistory"
                                :pagination="{ pageSize: 5 }"
                                row-key="id"
                                size="small"
                            >
                                <template #bodyCell="{ column, record }">
                                    <template v-if="column.key === 'actions'">
                                        <a-space>
                                            <a-button size="small" @click="downloadBackup(record)">
                                                <template #icon><download-outlined /></template>
                                            </a-button>
                                            <a-popconfirm
                                                title="确定要删除这个备份文件吗？"
                                                ok-text="确定"
                                                cancel-text="取消"
                                                @confirm="deleteBackup(record.filename)"
                                            >
                                                <a-button size="small" danger>
                                                    <template #icon><delete-outlined /></template>
                                                </a-button>
                                            </a-popconfirm>
                                        </a-space>
                                    </template>
  
                                    <template v-if="column.key === 'size'">
                                        {{ record.size }}
                                    </template>
                                </template>
                            </a-table>
                        </a-tab-pane>
  
                        <!-- 恢复标签页 -->
                        <a-tab-pane key="restore" tab="数据库恢复">
                            <a-alert
                                message="数据库恢复警告"
                                description="恢复操作将覆盖当前数据库中的所有数据，请谨慎操作！建议在执行恢复前先备份当前数据库。"
                                type="warning"
                                show-icon
                                style="margin-bottom: 20px"
                            />
  
                            <a-form layout="vertical">
                                <a-form-item label="选择备份文件">
                                    <a-upload
                                        v-model:file-list="restoreFileList"
                                        :before-upload="beforeUploadRestoreFile"
                                        accept=".sql"
                                        :max-count="1"
                                    >
                                        <a-button>
                                            <template #icon><upload-outlined /></template>
                                            选择SQL文件
                                        </a-button>
                                        <template #tip>
                                            <div class="ant-upload-text">
                                                请选择要恢复的SQL备份文件
                                            </div>
                                        </template>
                                    </a-upload>
                                </a-form-item>
  
                                <a-form-item>
                                    <a-button
                                        type="primary"
                                        danger
                                        @click="handleRestore"
                                        :loading="restoreLoading"
                                        :disabled="!restoreFileList.length"
                                    >
                                        <template #icon><upload-outlined /></template>
                                        执行恢复
                                    </a-button>
                                </a-form-item>
                            </a-form>
  
                            <a-divider />
  
                            <h3>恢复前检查</h3>
                            <a-descriptions bordered :column="1" v-if="restoreFileList.length">
                                <a-descriptions-item label="文件名">
                                    {{ restoreFileList[0].name }}
                                </a-descriptions-item>
                                <a-descriptions-item label="文件大小">
                                    {{ formatFileSize(restoreFileList[0].size) }}
                                </a-descriptions-item>
                                <a-descriptions-item label="最后修改时间">
                                    {{ formatDate(restoreFileList[0].lastModifiedDate) }}
                                </a-descriptions-item>
                            </a-descriptions>
                            <a-empty v-else description="请先选择要恢复的SQL文件" />
                        </a-tab-pane>
                    </a-tabs>
                </a-card>
            </div>
        </a-layout-content>
  
        <!-- 备份确认对话框 -->
        <a-modal
            v-model:visible="backupConfirmVisible"
            title="确认数据库备份"
            ok-text="确认备份"
            cancel-text="取消"
            @ok="confirmBackup"
            @cancel="backupConfirmVisible = false"
        >
            <p>确定要执行数据库备份操作吗？</p>
            <p>备份文件将保存到: <strong>{{ backupPath || defaultBackupPath }}</strong></p>
        </a-modal>
  
        <!-- 恢复确认对话框 -->
        <a-modal
            v-model:visible="restoreConfirmVisible"
            title="确认数据库恢复"
            ok-text="确认恢复"
            cancel-text="取消"
            ok-type="danger"
            @ok="confirmRestore"
            @cancel="restoreConfirmVisible = false"
        >
            <a-alert
                message="警告"
                description="此操作将覆盖当前数据库中的所有数据，且不可撤销！"
                type="error"
                show-icon
                style="margin-bottom: 15px"
            />
            <p>确定要使用以下文件恢复数据库吗？</p>
            <p>恢复文件: <strong>{{ restoreFileList[0]?.name }}</strong></p>
        </a-modal>
    </a-layout>
  </template>
  
  <script setup>
  import { ref, reactive, computed, onMounted } from 'vue';
  import { useRouter } from 'vue-router';
  import { 
    ArrowLeftOutlined,
    DownloadOutlined,
    UploadOutlined,
    DeleteOutlined,
    ExclamationCircleOutlined
  } from '@ant-design/icons-vue';
  import { message, Modal } from 'ant-design-vue';
  import dayjs from 'dayjs';
  import { createBackup, getBackup, deleteBackup, restoreBackup } from '@/api/api'; // 引入实际 API
  
  const router = useRouter();
  
  // 标签页状态
  const activeTab = ref('backup');
  
  // 备份相关状态
  const backupFileName = computed(() => {
    return `hospital_db_backup_${dayjs().format('YYYY-MM-DD-HH-mm-ss')}.sql`;
  });
  const backupPath = ref('');
  const defaultBackupPath = ref('E:/download/');
  const backupLoading = ref(false);
  const backupConfirmVisible = ref(false);
  
  // 恢复相关状态
  const restoreFileList = ref([]);
  const restoreLoading = ref(false);
  const restoreConfirmVisible = ref(false);
  
  // 备份历史记录
  const backupHistory = ref([]);
  
  // 表格列定义
  const backupColumns = [
    {
        title: '备份文件名',
        dataIndex: 'filename',
        key: 'filename'
    },
    {
        title: '备份时间',
        dataIndex: 'backup_time',
        key: 'backup_time',
        width: 180
    },
    {
        title: '文件大小',
        key: 'size',
        width: 120
    },
    {
        title: '操作',
        key: 'actions',
        width: 120
    }
  ];
  
  // 格式化文件大小
  const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
  };
  
  // 格式化日期
  const formatDate = (date) => {
    return dayjs(date).format('YYYY-MM-DD HH:mm:ss');
  };
  
  // 处理备份操作
  const handleBackup = () => {
    backupConfirmVisible.value = true;
  };
  
  const confirmBackup = async () => {
    try {
        backupLoading.value = true;
        backupConfirmVisible.value = false;
  
        const response = await createBackup();
        if (response.data.code === 200) {
            const newBackup = {
                id: Math.max(...backupHistory.value.map(b => b.id), 0) + 1,
                filename: response.data.data.backupFileName,
                path: `${backupPath.value || defaultBackupPath.value}${response.data.data.backupFileName}`,
                size: response.data.data.fileSize,
                backup_time: response.data.data.backupTime
            };
  
            backupHistory.value.unshift(newBackup);
            message.success('数据库备份成功');
        } else {
            message.error('数据库备份失败');
        }
    } catch (error) {
        message.error('数据库备份失败');
    } finally {
        backupLoading.value = false;
    }
  };
  
  // 处理恢复操作
  const beforeUploadRestoreFile = (file) => {
    // 阻止默认上传行为，我们将在恢复确认后处理
    return false;
  };
  
  const handleRestore = () => {
    if (!restoreFileList.value.length) {
        message.warning('请先选择要恢复的SQL文件');
        return;
    }
  
    restoreConfirmVisible.value = true;
  };
  
  const confirmRestore = async () => {
    try {
        restoreLoading.value = true;
        restoreConfirmVisible.value = false;
  
        const backupFileName = restoreFileList.value[0].name;
        const response = await restoreBackup(backupFileName);
        if (response.data.code === 200) {
            message.success('数据库恢复成功');
            restoreFileList.value = [];
        } else {
            message.error('数据库恢复失败');
        }
    } catch (error) {
        message.error('数据库恢复失败');
    } finally {
        restoreLoading.value = false;
    }
  };
  
  // 下载备份文件
  const downloadBackup = (record) => {
    message.info(`开始下载: ${record.filename}`);
    // 实际项目中这里应该调用下载API
  };
  
  // 删除备份文件
  const deleteBackupFile = async (backupFileName) => {
    try {
        const response = await deleteBackup(backupFileName);
        if (response.data.code === 200) {
            backupHistory.value = backupHistory.value.filter(b => b.filename !== backupFileName);
            message.success('备份文件删除成功');
        } else {
            message.error('删除备份文件失败');
        }
    } catch (error) {
        message.error('删除备份文件失败');
    }
  };
  
  // 初始化
  onMounted(async () => {
    try {
        const response = await getBackup();
        if (response.data.code === 200) {
            backupHistory.value = response.data.data.map((item, index) => ({
                id: index + 1,
                ...item
            }));
        }
    } catch (error) {
        message.error('获取备份记录失败');
    }
  });
  </script>
  
  <style scoped>
  .database-management {
    min-height: 100vh;
    background: #f0f2f5;
  }
  
  .page-header {
    background: #fff;
    padding: 16px 24px;
    margin-bottom: 1px;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
  }
  
  .content {
    padding: 24px;
  }
  
  .container {
    max-width: 1200px;
    margin: 0 auto;
  }
  
  .database-card {
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  }
  
  .ant-upload-text {
    color: rgba(0, 0, 0, 0.45);
    font-size: 14px;
    margin-top: 8px;
  }
  
  @media (max-width: 768px) {
    .content {
        padding: 12px;
    }
  }
  </style>    