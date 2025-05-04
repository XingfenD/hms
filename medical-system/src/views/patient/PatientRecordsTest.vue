<template>
  <div class="medical-record-container">
    <a-card title="我的就诊记录" :bordered="false">
      <!-- 筛选区域 -->
      <div class="filter-container">
        <a-form layout="inline" :model="filterForm">
          <a-form-item label="就诊日期">
            <a-range-picker
              v-model:value="filterForm.dateRange"
              style="width: 240px"
              :disabled-date="disabledDate"
            />
          </a-form-item>
          <a-form-item label="科室">
            <a-select
              v-model:value="filterForm.department"
              style="width: 150px"
              placeholder="全部科室"
              allow-clear
            >
              <a-select-option 
                v-for="dept in departments" 
                :key="dept.id"
                :value="dept.id"
              >
                {{ dept.name }}
              </a-select-option>
            </a-select>
          </a-form-item>
          <a-form-item label="医生">
            <a-select
              v-model:value="filterForm.doctor"
              style="width: 150px"
              placeholder="全部医生"
              allow-clear
            >
              <a-select-option 
                v-for="doctor in doctors" 
                :key="doctor.id"
                :value="doctor.id"
              >
                {{ doctor.name }}
              </a-select-option>
            </a-select>
          </a-form-item>
          <a-form-item>
            <a-button type="primary" @click="handleSearch">
              <template #icon><SearchOutlined /></template>
              查询
            </a-button>
            <a-button style="margin-left: 8px" @click="resetSearch">
              <template #icon><RedoOutlined /></template>
              重置
            </a-button>
          </a-form-item>
        </a-form>
      </div>

      <!-- 就诊记录表格 -->
      <a-table
        :columns="columns"
        :data-source="medicalRecords"
        :row-key="record => record.id"
        :pagination="pagination"
        :loading="loading"
        @change="handleTableChange"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'date'">
            {{ formatDate(record.date) }}
          </template>
          <template v-else-if="column.key === 'doctor'">
            {{ record.doctor.name }} {{ record.doctor.title }}
          </template>
          <template v-else-if="column.key === 'status'">
            <a-tag :color="getStatusColor(record.status)">
              {{ record.status }}
            </a-tag>
          </template>
          <template v-else-if="column.key === 'action'">
            <a-space>
              <a-button size="small" @click="viewRecordDetail(record)">
                查看详情
              </a-button>
              <a-button 
                size="small" 
                type="primary" 
                @click="downloadMedicalRecord(record)"
                :disabled="record.status !== 1"
              >
                下载病历
              </a-button>
            </a-space>
          </template>
        </template>
      </a-table>
    </a-card>

    <!-- 就诊详情模态框 -->
    <a-modal
      v-model:visible="detailVisible"
      :title="currentRecord ? `就诊详情 - ${formatDate(currentRecord.date)}` : ''"
      width="800px"
      :footer="null"
    >
      <MedicalRecordDetail v-if="currentRecord" :record="currentRecord" />
    </a-modal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { message } from 'ant-design-vue'
import dayjs from 'dayjs'
import { SearchOutlined, RedoOutlined } from '@ant-design/icons-vue'
import MedicalRecordDetail from '../../components/patient/MedicalRecordDetail.vue'
import { getAppoint, getLoginInfo, getAppointinfo } from '@/api/api.js'



// 表格列定义
const columns = [
  {
    title: '就诊日期',
    dataIndex: 'date',
    key: 'date',
    width: 120,
    sorter: true
  },
  {
    title: '科室',
    dataIndex: 'department',
    key: 'department',
    width: 120
  },
  {
    title: '医生',
    dataIndex: 'doctor',
    key: 'doctor',
    width: 150
  },
  {
    title: '诊断结果',
    dataIndex: 'diagnosis',
    key: 'diagnosis',
    ellipsis: true
  },
  {
    title: '状态',
    dataIndex: 'status',
    key: 'status',
    width: 100
  },
  {
    title: '操作',
    key: 'action',
    width: 150
  }
]

// 分页配置
const pagination = ref({
  current: 1,
  pageSize: 10,
  total: 0,
  showSizeChanger: true,
  pageSizeOptions: ['10', '20', '50'],
  showTotal: total => `共 ${total} 条`
})

// 筛选表单
const filterForm = ref({
  dateRange: [],
  department: undefined,
  doctor: undefined
})

// 数据状态
const medicalRecords = ref([])
const loading = ref(false)
const detailVisible = ref(false)
const currentRecord = ref(null)

// 科室和医生数据
const departments = ref([
  { id: 1, name: '内科' },
  { id: 2, name: '外科' },
  { id: 3, name: '儿科' },
  { id: 4, name: '妇产科' },
  { id: 5, name: '眼科' }
])

const doctors = ref([
  { id: 1, name: '张医生', title: '主任医师' },
  { id: 2, name: '李医生', title: '副主任医师' },
  { id: 3, name: '王医生', title: '主治医师' },
  { id: 4, name: '刘医生', title: '住院医师' }
])

// 初始化加载数据
onMounted(async () => {
  try {
    const loginInfoResponse = await getLoginInfo()
    if (loginInfoResponse.data.code === 200) {
      const patientId = loginInfoResponse.data.data.user_id
      await fetchMedicalRecords(patientId)
    } else {
      message.error('获取登录信息失败')
    }
  } catch (error) {
    message.error('初始化失败: ' + error.message)
  }
})

const getStatusText = status_code => {
  const map = {
    0: '已预约',
    1: '正在进行',
    2: '已结束',
    3: '已过号',
    4: '已签到'
  }
  return map[status_code]
}

const getStatusColor = status_code => {
  const map = {
    '已预约': 'blue',
    '正在进行': 'orange',
    '已结束': 'grey',
    '已过号': 'red',
    '已签到': 'green'
  }
  return map[status_code]
}

// 获取就诊记录
const fetchMedicalRecords = async (patientId) => {
  try {
    loading.value = true
    const appointResponse = await getAppoint({ patient_id: patientId })
    if (appointResponse.data.code === 200) {
      const records = await Promise.all(appointResponse.data.data.map(async item => {
        const apId = item.app_if.AppointmentID
        const appointInfoResponse = await getAppointinfo(apId)
        const infoData = appointInfoResponse.data.code === 200 ? appointInfoResponse.data.data : {}
        return {
          id: apId,
          date: item.app_if.AppointmentDateTime,
          department: item.doc_if.DoctorDepartmentName,
          doctor: {
            name: item.doc_if.DoctorName,
            title: item.doc_if.DoctorTitle
          },
          diagnosis: infoData.pat_now_history || '暂无',
          status: getStatusText(item.app_if.AppointmentStatus),
          symptoms: infoData.pat_requirements || '暂无',
          medicalHistory: infoData.pat_history || '暂无',
          treatmentPlan: infoData.treat_method || '暂无',
          prescriptions: []
        }
      }))
      medicalRecords.value = records
      pagination.value.total = records.length
    } else {
      message.error('获取就诊记录失败')
    }
  } catch (error) {
    message.error('获取就诊记录失败: ' + error.message)
  } finally {
    loading.value = false
  }
}

// 格式化日期
const formatDate = date => {
  return date ? dayjs(date).format('YYYY-MM-DD') : '-'
}

// 禁用日期
const disabledDate = current => {
  return current && current > dayjs().endOf('day')
}

// 处理查询
const handleSearch = async () => {
  try {
    const loginInfoResponse = await getLoginInfo()
    if (loginInfoResponse.data.code === 200) {
      const patientId = loginInfoResponse.data.data.user_id
      pagination.value.current = 1
      await fetchMedicalRecords(patientId)
    } else {
      message.error('获取登录信息失败')
    }
  } catch (error) {
    message.error('查询失败: ' + error.message)
  }
}

// 重置查询
const resetSearch = async () => {
  filterForm.value = {
    dateRange: [],
    department: undefined,
    doctor: undefined
  }
  await handleSearch()
}

// 处理表格变化
const handleTableChange = async (pag, filters, sorter) => {
  try {
    const loginInfoResponse = await getLoginInfo()
    if (loginInfoResponse.data.code === 200) {
      const patientId = loginInfoResponse.data.data.user_id
      pagination.value.current = pag.current
      pagination.value.pageSize = pag.pageSize
      await fetchMedicalRecords(patientId)
    } else {
      message.error('获取登录信息失败')
    }
  } catch (error) {
    message.error('表格数据更新失败: ' + error.message)
  }
}

// 查看记录详情
const viewRecordDetail = record => {
  currentRecord.value = record
  detailVisible.value = true
}

// 下载病历
const downloadMedicalRecord = record => {
  message.loading({ content: '正在生成病历文件...', key: 'download' })
  // 模拟下载延迟
  setTimeout(() => {
    message.success({ content: `病历 ${record.id} 下载成功`, key: 'download' })
  }, 1500)
}
</script>

<style scoped>
.medical-record-container {
  padding: 24px;
}

.filter-container {
  margin-bottom: 24px;
}

.ant-descriptions-item {
  padding: 12px 24px;
}

.ant-descriptions-item-label {
  width: 120px;
  font-weight: 500;
}
</style>    