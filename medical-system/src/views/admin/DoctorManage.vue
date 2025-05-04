<template>
    <a-layout class="checkup-management">
      <!-- 顶部导航 -->
      <a-page-header
        title="检查项目管理"
        sub-title="管理检查项目信息"
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
            <a-breadcrumb-item>检查项目管理</a-breadcrumb-item>
          </a-breadcrumb>
        </template>
        <template #extra v-if="selectedTab === 'checkup-items'">
          <a-space>
            <a-button type="primary" @click="showAddCheckupModal">
              <template #icon><plus-outlined /></template>
              新增检查项目
            </a-button>
          </a-space>
        </template>
      </a-page-header>
  
      <a-layout>
        <!-- 侧边栏 -->
        <a-layout-sider width="200px" theme="light" :collapsible="false" class="site-layout-sider">
          <a-menu mode="inline" default-selected-keys="['checkup-items']" @select="handleTabChange" class="sidebar-menu">
            <a-menu-item key="checkup-items">
              <template #icon><eye-outlined /></template>
              检查项目列表
            </a-menu-item>
            <a-menu-item key="checkup-records">
              <template #icon><eye-outlined /></template>
              检查记录列表
            </a-menu-item>
          </a-menu>
        </a-layout-sider>
  
        <a-layout-content class="content">
          <div class="container">
            <!-- 检查项目列表卡片 -->
            <div v-if="selectedTab === 'checkup-items'" class="view-container">
              <a-card title="检查项目列表" class="checkup-card">
                <template #extra>
                  <a-input-search
                    v-model:value="searchText"
                    placeholder="搜索检查项目名称..."
                    style="width: 300px"
                    @search="handleSearch"
                  />
                </template>
  
                <a-table
                  :columns="columns"
                  :data-source="filteredCheckups"
                  :pagination="{ pageSize: 10, showSizeChanger: true }"
                  row-key="exam_def_id"
                  bordered
                >
                  <!-- 操作列 -->
                  <template #bodyCell="{ column, record }">
                    <template v-if="column.key === 'actions'">
                      <a-space>
                        <a-button size="small" @click="editCheckup(record)">
                          <template #icon><edit-outlined /></template>
                        </a-button>
                        <a-popconfirm
                          title="确定要删除该检查项目吗？"
                          ok-text="确定"
                          cancel-text="取消"
                          @confirm="deleteCheckup(record.exam_def_id)"
                        >
                          <a-button size="small" danger>
                            <template #icon><delete-outlined /></template>
                          </a-button>
                        </a-popconfirm>
                      </a-space>
                    </template>
                  </template>
                </a-table>
              </a-card>
            </div>
  
            <!-- 检查记录列表卡片 -->
            <div v-if="selectedTab === 'checkup-records'" class="view-container">
              <a-card title="检查记录列表" class="checkup-card">
                <template #extra>
                  <a-button type="primary" @click="fetchExamRecords">
                    <template #icon><reload-outlined /></template>
                    刷新记录
                  </a-button>
                </template>
  
                <a-table
                  :columns="recordColumns"
                  :data-source="examRecordsWithNames"
                  :pagination="{ pageSize: 10, showSizeChanger: true }"
                  row-key="exam_id"
                  bordered
                >
                  <template #bodyCell="{ column, record }">
                    <template v-if="column.key === 'status'">
                      <a-tag :color="getStatusColor(record.MaxStatusCode)">
                        {{ getStatusText(record.MaxStatusCode) }}
                      </a-tag>
                    </template>
                    <template v-if="column.key === 'actions'">
                      <a-space>
                        <a-button
                          size="small"
                          :disabled="record.MaxStatusCode!== 0"
                          @click="payForCheckup(record.exam_id)"
                        >
                          付款
                        </a-button>
                        <a-button
                          size="small"
                          :disabled="record.MaxStatusCode!== 1"
                          @click="completeCheckupRecord(record.exam_id)"
                        >
                          完成检查
                        </a-button>
                        <a-button
                          size="small"
                          :disabled="record.MaxStatusCode!== 2"
                          @click="showCheckupResultModal(record)"
                        >
                          填写/查看结果
                        </a-button>
                      </a-space>
                    </template>
                  </template>
                </a-table>
              </a-card>
            </div>
          </div>
        </a-layout-content>
      </a-layout>
  
      <!-- 新增/编辑检查项目模态框 -->
      <a-modal
        v-model:visible="modalVisible"
        :title="currentCheckup? `编辑检查项目 (ID: ${currentCheckup.exam_def_id})` : '新增检查项目'"
        width="800px"
        :mask-closable="false"
        :destroy-on-close="true"
        @ok="handleSubmit"
        @cancel="resetForm"
      >
        <a-form
          ref="formRef"
          :model="formState"
          :rules="rules"
          layout="vertical"
        >
          <a-row :gutter="24">
            <a-col :span="12">
              <a-form-item label="项目名称" name="exam_name">
                <a-input v-model:value="formState.exam_name" placeholder="请输入项目名称" />
              </a-form-item>
            </a-col>
            <a-col :span="12">
              <a-form-item label="项目价格" name="exam_price">
                <a-input v-model:value="formState.exam_price" placeholder="请输入项目价格" />
              </a-form-item>
            </a-col>
          </a-row>
        </a-form>
      </a-modal>
  
      <!-- 填写/查看检查结果模态框 -->
      <a-modal
        v-model:visible="resultModalVisible"
        :title="`检查记录 (ID: ${currentExamRecord?.exam_id}) 结果`"
        width="800px"
        :mask-closable="false"
        :destroy-on-close="true"
        @ok="saveCheckupResult"
        @cancel="hideCheckupResultModal"
      >
        <a-form
          ref="resultFormRef"
          :model="resultFormState"
          :rules="resultRules"
          layout="vertical"
        >
          <a-form-item label="检查结果" name="exam_result">
            <a-textarea
              v-model:value="resultFormState.exam_result"
              placeholder="请输入检查结果"
              :rows="4"
            />
          </a-form-item>
        </a-form>
      </a-modal>
    </a-layout>
  </template>
  
  <script setup>
  import { ref, reactive, computed, onMounted } from 'vue';
  import { useRouter } from 'vue-router';
  import {
    ArrowLeftOutlined,
    PlusOutlined,
    EditOutlined,
    DeleteOutlined,
    ReloadOutlined,
    EyeOutlined
  } from '@ant-design/icons-vue';
  import { message } from 'ant-design-vue';
  import {
    getCheckup,
    addCheckupRecord,
    getExamRecord,
    getCheckupResult,
    payCheckup,
    completeCheckup,
    addCheckupResult,
    updateCheckupResult,
    addCheckup,
    updateCheckup,
    deleteCheck
  } from '@/api/api';
  
  const router = useRouter();
  
  // 状态管理
  const checkups = ref([]);
  const searchText = ref('');
  const currentCheckup = ref(null);
  const modalVisible = ref(false);
  const examRecords = ref([]);
  const currentExamRecord = ref(null);
  const resultModalVisible = ref(false);
  const selectedTab = ref('checkup-items');
  
  // 表单状态
  const formState = reactive({
    exam_def_id: null,
    exam_name: '',
    exam_price: ''
  });
  
  const resultFormState = reactive({
    exam_id: null,
    exam_result: ''
  });
  
  // 验证规则
  const rules = {
    exam_name: [{ required: true, message: '请输入检查项目名称' }],
    exam_price: [
      { required: true, message: '请输入检查项目价格' },
      { pattern: /^\d+(\.\d{1,2})?$/, message: '请输入有效的价格' }
    ]
  };
  
  const resultRules = {
    exam_result: [{ required: true, message: '请输入检查结果' }]
  };
  
  // 表格列配置
  const columns = [
    { title: '项目ID', dataIndex: 'exam_def_id', width: 100 },
    { title: '项目名称', dataIndex: 'exam_name', width: 200 },
    { title: '项目价格', dataIndex: 'exam_price', width: 150 },
    { title: '操作', key: 'actions', width: 180 }
  ];
  
  const recordColumns = [
    { title: '检查记录ID', dataIndex: 'exam_id', width: 100 },
    { title: '项目ID', dataIndex: 'exam_def_id', width: 100 },
    { title: '项目名称', dataIndex: 'exam_name', width: 200 },
    { title: '状态', dataIndex: 'MaxStatusCode', key: 'status', width: 120 },
    { title: '状态更新时间', dataIndex: 'MaxStatusCodeTime', width: 200 },
    { title: '操作', key: 'actions', width: 250 }
  ];
  
  // 计算属性
  const filteredCheckups = computed(() => {
    const search = searchText.value.toLowerCase();
    return checkups.value.filter(c =>
      c.exam_name.toLowerCase().includes(search)
    );
  });
  
  const examRecordsWithNames = computed(() => {
    return examRecords.value.map(record => {
      const checkup = checkups.value.find(item => item.exam_def_id === record.exam_def_id);
      return {
        ...record,
        exam_name: checkup? checkup.exam_name : '未知项目'
      };
    });
  });
  
  // 方法
  const fetchCheckups = async () => {
    try {
      const response = await getCheckup({ exam_name: searchText.value });
      if (response.data.code === 200) {
        checkups.value = response.data.data;
      } else {
        message.error('获取检查项目列表失败');
      }
    } catch (error) {
      message.error('获取检查项目列表失败：' + error.message);
    }
  };
  
  const showAddCheckupModal = () => {
    currentCheckup.value = null;
    resetForm();
    modalVisible.value = true;
  };
  
  const editCheckup = checkup => {
    currentCheckup.value = checkup;
    Object.assign(formState, {
      ...checkup
    });
    modalVisible.value = true;
  };
  
  const handleSubmit = async () => {
    try {
      if (currentCheckup.value) {
        const formData = new FormData();
        formData.append("exam_def_id", formState.exam_def_id);
        formData.append("new_exam_name", formState.exam_name);
        formData.append("new_exam_price", formState.exam_price);
        const response = await updateCheckup(formData);
        console.log(response.data, formState.exam_name, formState.exam_price)
        if (response.data.code !== 200) {
          message.error("编辑检查记录失败");
        }        
        // 编辑检查项目，这里简单模拟更新本地数据，实际需要调用更新 API
        const index = checkups.value.findIndex(c => c.exam_def_id === currentCheckup.value.exam_def_id);
        checkups.value.splice(index, 1, { ...formState });
      } else {
        const formData = new FormData();
        formData.append("exam_name", formState.exam_name);
        formData.append("exam_price", formState.exam_price);
        const response = await addCheckup(formData);
        console.log(response.data, formState.exam_price, formState.exam_name)
        if (response.data.code !== 200) {
          message.error("新增检查记录失败");
        }
        // 新增检查项目，这里简单模拟添加本地数据，实际需要调用新增 API
        // const newCheckup = {
        //   exam_def_id: Math.max(...checkups.value.map(c => c.exam_def_id), 0) + 1,
        //   ...formState
        // };
        // checkups.value.unshift(newCheckup);
      }
      modalVisible.value = false;
      fetchCheckups();
      resetForm();
      message.success(currentCheckup.value? '检查项目信息更新成功' : '检查项目添加成功');
    } catch (error) {
      message.error('操作失败' + error.message);
    }
  };
  
  const deleteCheckup = async id => {
    try {
      // 这里简单模拟删除本地数据，实际需要调用删除 API
      // checkups.value = checkups.value.filter(c => c.exam_def_id!== id);
      const response = await deleteCheck(id);
      if (response.data.code !== 200) {
        message.error('删除失败');
      }
      fetchCheckups();
      message.success('删除成功');
    } catch (error) {
      message.error('删除失败');
    }
  };
  
  const resetForm = () => {
    formState.exam_def_id = null;
    formState.exam_name = '';
    formState.exam_price = '';
  };
  
  const handleSearch = () => {
    fetchCheckups();
  };
  
  const fetchExamRecords = async () => {
    try {
      const response = await getExamRecord();
      if (response.data.code === 200) {
        examRecords.value = response.data.data;
      } else {
        message.error('获取检查记录列表失败');
      }
    } catch (error) {
      message.error('获取检查记录列表失败：' + error.message);
    }
  };
  
  const getStatusText = statusCode => {
    const statusMap = {
      0: '未检查',
      1: '已付款',
      2: '已付款已检查'
    };
    return statusMap[statusCode] || '未知状态';
  };
  
  const getStatusColor = statusCode => {
    const colorMap = {
      0: 'red',
      1: 'orange',
      2: 'green'
    };
    return colorMap[statusCode] || 'default';
  };
  
  const payForCheckup = async examId => {
    try {
      const response = await payCheckup(examId);
      if (response.data.code === 200) {
        const index = examRecords.value.findIndex(record => record.exam_id === examId);
        if (index!== -1) {
          examRecords.value[index].MaxStatusCode = 1;
          examRecords.value[index].MaxStatusCodeTime = new Date().toLocaleString();
        }
        message.success('检查记录付款成功');
      } else {
        message.error('检查记录付款失败');
      }
    } catch (error) {
      message.error('检查记录付款失败：' + error.message);
    }
  };
  
  const completeCheckupRecord = async examId => {
    try {
      const response = await completeCheckup(examId);
      if (response.data.code === 200) {
        const index = examRecords.value.findIndex(record => record.exam_id === examId);
        if (index!== -1) {
          examRecords.value[index].MaxStatusCode = 2;
          examRecords.value[index].MaxStatusCodeTime = new Date().toLocaleString();
        }
        message.success('完成检查');
      } else {
        message.error('完成检查失败');
      }
    } catch (error) {
      message.error('完成检查失败：' + error.message);
    }
  };
  
  const showCheckupResultModal = async record => {
    currentExamRecord.value = record;
    resultFormState.exam_id = record.exam_id;
    try {
      const response = await getCheckupResult(record.exam_id);
      console.log(response.data)
      if (response.data.code === 200) {
        resultFormState.exam_result = response.data.data.exam_result;
      } else {
        resultFormState.exam_result = '';
      }
    } catch (error) {
      resultFormState.exam_result = '';
    }
    resultModalVisible.value = true;
  };
  
  const hideCheckupResultModal = () => {
    resultModalVisible.value = false;
    resultFormState.exam_result = '';
  };
  
  const saveCheckupResult = async () => {
    try {
      const formData = new FormData();
      formData.append('exam_id', resultFormState.exam_id);
      formData.append('exam_result', resultFormState.exam_result);
      let response;
      const ResultBool = await getCheckupResult(resultFormState.exam_id);
      if (ResultBool.data.code == 200) {
        response = await updateCheckupResult(formData);
      } else {
        response = await addCheckupResult(formData);
      }
      console.log(response.data, resultFormState.exam_result, resultFormState.exam_id)
      if (response.data.code === 200) {
        message.success('检查结果保存成功');
        hideCheckupResultModal();
      } else {
        message.error('检查结果保存失败');
      }
    } catch (error) {
      message.error('检查结果保存失败：' + error.message);
    }
  };
  
  const handleTabChange = ({ key }) => {
    selectedTab.value = key;
  };
  
  onMounted(() => {
    fetchCheckups();
    fetchExamRecords();
  });
  </script>
  
  <style scoped>
  .checkup-management {
    min-height: 100vh;
    background: #f0f2f5;
  }
  
  .site-layout-sider {
    background: #f8f9fa;
    border-right: 1px solid #e8e8e8;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: width 0.3s ease;
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
    max-width: 1400px;
    margin: 0 auto;
  }
  
  .checkup-card {
    margin-bottom: 24px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  }
  
  .sidebar-menu {
    border-right: none;
  }
  
  .sidebar-menu .ant-menu-item {
    padding-left: 24px;
    font-size: 18px;
    color: #333;
    transition: all 0.3s;
    position: relative;
  }
  
  .sidebar-menu .ant-menu-item::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    height: 100%;
    width: 3px;
    background-color: #1890ff;
    opacity: 0;
    transition: opacity 0.3s;
  }
  
  .sidebar-menu .ant-menu-item:hover {
    background-color: #f0f2f5;
    color: #1890ff;
  }
  
  .sidebar-menu .ant-menu-item:hover::before {
    opacity: 1;
  }
  
  .sidebar-menu .ant-menu-item-selected {
    background-color: #e6f7ff;
    color: #1890ff;
    border-right: none;
  }
  
  .sidebar-menu .ant-menu-item-selected::before {
    opacity: 1;
  }
  
  .sidebar-menu .ant-menu-item-icon {
    font-size: 18px;
    margin-right: 12px;
    color: #666;
    transition: color 0.3s;
  }
  
  .sidebar-menu .ant-menu-item:hover .ant-menu-item-icon,
  .sidebar-menu .ant-menu-item-selected .ant-menu-item-icon {
    color: #1890ff;
  }
  
  @media (max-width: 768px) {
    .content {
      padding: 12px;
    }
  }
  </style>    